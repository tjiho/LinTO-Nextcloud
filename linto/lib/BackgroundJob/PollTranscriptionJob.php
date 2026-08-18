<?php

declare(strict_types=1);

namespace OCA\LinTO\BackgroundJob;

use OCA\LinTO\AppInfo\Application;
use OCA\LinTO\Db\TranscribeJobMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\IJobList;
use OCP\BackgroundJob\Job;
use OCP\Files\IRootFolder;
use OCP\Http\Client\IClientService;
use OCP\IConfig;
use OCP\IURLGenerator;
use OCP\Notification\IManager;
use Psr\Log\LoggerInterface;
use ZipArchive;

class PollTranscriptionJob extends Job {
	public function __construct(
		ITimeFactory $time,
		private IClientService $clientService,
		private TranscribeJobMapper $mapper,
		private IConfig $config,
		private LoggerInterface $logger,
		private IManager $notificationManager,
		private IRootFolder $rootFolder,
		private IURLGenerator $urlGenerator,
		private IJobList $jobList,
	) {
		parent::__construct($time);
	}

	/**
	 * Check if transcription is done
	 * Response structure: {"jobs": {"transcription": {"state": "done"}}}
	 */
	private function isTranscriptionDone(array $data): bool {
		$job = ($data['jobs'] ?? [])['transcription'] ?? null;
		return ($job['state'] ?? null) === 'done';
	}

	protected function run($argument): void {
		$jobId = $argument['jobId'] ?? null;
		$this->logger->info("running job for LinTO");
		if ($jobId === null) {
			$this->logger->error('PollTranscriptionJob: jobId missing');
			$this->jobList->remove($this, $argument);
			return;
		}

		try {
			$entity = $this->mapper->find((int)$jobId);
			if ($entity === null) {
				$this->logger->error('PollTranscriptionJob: job not found for id ' . $jobId);
				$this->jobList->remove($this, $argument);
				return;
			}

			$apiUrl = rtrim($this->config->getAppValue(Application::APP_ID, 'apiUrl', 'https://studio.linto.ai/cm-api/api'), '/');
			$orgId = $this->config->getAppValue(Application::APP_ID, 'organisationId', '');
			$apiKey = $this->config->getAppValue(Application::APP_ID, 'apiKey', '');

			if (empty($apiUrl) || empty($orgId) || empty($apiKey) || empty($entity->getConversationId())) {
				$this->logger->error('PollTranscriptionJob: missing configuration');
				$this->jobList->remove($this, $argument);
				return;
			}

			$client = $this->clientService->newClient();
			$url = $apiUrl . '/conversations/' . $entity->getConversationId();

			$response = $client->get($url, [
				'headers' => [
					'Authorization' => 'Bearer ' . $apiKey,
					'Content-Type' => 'application/json',
				],
			]);

			$this->logger->debug('Get on ' . $url);

			if ($response->getStatusCode() !== 200) {
				$this->logger->error('PollTranscriptionJob: API error ' . $response->getStatusCode());
				// On laisse le job dans la liste, il sera rejoué au prochain passage cron
				return;
			}

			$body = $response->getBody();
			if (is_resource($body)) {
				$body = stream_get_contents($body);
			}

			$data = json_decode((string)$body, true);
			if ($data === null) {
				$this->logger->error('PollTranscriptionJob: invalid JSON response');
				return;
			}

			$state = ($data['jobs'] ?? [])['transcription']['state'] ?? 'pending';

			$entity->setStatus($state);
			$entity->setUpdatedAt(new \DateTime());

			// Store full JSON response in transcript
			$entity->setTranscript(json_encode($data));

			$this->mapper->update($entity);

			if (!$this->isTranscriptionDone($data)) {
				// Transcription pas encore terminée : on laisse le job en liste,
				// il sera rejoué au prochain passage du cron système.
				$this->logger->info('PollTranscriptionJob: transcription waiting for job ' . $jobId);
				return;
			}

			$this->logger->info('PollTranscriptionJob: transcription done for job ' . $jobId);

			// Terminé : on retire le job de la liste dès maintenant pour éviter
			// tout risque de repasser dessus si le reste du traitement échoue puis est retenté.
			$this->jobList->remove($this, $argument);

			// 1. Send notification
			$notification = $this->notificationManager->createNotification();
			$notification->setApp(Application::APP_ID)
				->setUser($entity->getUserId())
				->setDateTime(new \DateTime())
				->setObject('transcription', (string)$entity->getId())
				->setSubject('transcription_done', [
					'message' => 'Votre fichier a été transcrit avec succès',
				])
				->setLink($this->urlGenerator->linkToRouteAbsolute('linto.page.index'));

			$this->notificationManager->notify($notification);

			// 2. Get source file info first (for metadata)
			$userId = $entity->getUserId();
			$fileName = 'transcript';
			if (!empty($userId)) {
				$userFolder = $this->rootFolder->getUserFolder($userId);
				$nodes = $userFolder->getById($entity->getFileId());
				if (!empty($nodes)) {
					$fileName = $nodes[0]->getName();
				}
			}

			// 3. Download audio from Linto Studio API
			$audioUrl = $url . '/media';
			$audioContent = '';
			try {
				$audioResponse = $client->get($audioUrl, [
					'headers' => [
						'Authorization' => 'Bearer ' . $apiKey,
					],
				]);
				if ($audioResponse->getStatusCode() === 200) {
					$audioBody = $audioResponse->getBody();
					$audioContent = is_resource($audioBody) ? stream_get_contents($audioBody) : $audioBody;
				} else {
					$this->logger->warning('PollTranscriptionJob: Failed to download audio for job ' . $jobId . ', status: ' . $audioResponse->getStatusCode());
				}
			} catch (\Throwable $e) {
				$this->logger->warning('PollTranscriptionJob: Audio download failed for job ' . $jobId . ': ' . $e->getMessage());
			}

			// 4. Create ZIP with transcript + audio (if available)
			$zip = new ZipArchive();
			$tmpZipPath = tempnam(sys_get_temp_dir(), 'linto_zip_');

			if ($zip->open($tmpZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
				$this->logger->error('PollTranscriptionJob: Failed to create ZIP for job ' . $jobId);
				return;
			}

			$zip->addFromString('transcript.json', json_encode($data, JSON_PRETTY_PRINT));
			
			if (!empty($audioContent)) {
				$zip->addFromString('audio.mp3', $audioContent);
			}

			$metadata = [
				'fileName' => $fileName,
				'createdAt' => (new \DateTime())->format(\DateTimeInterface::ATOM),
				'source' => 'linto-studio',
				'hasAudio' => !empty($audioContent),
			];
			$zip->addFromString('metadata.json', json_encode($metadata));

			$zip->close();

			// 5. Save ZIP as .transcript file
			if (!empty($userId) && !empty($nodes)) {
				$file = $nodes[0];
				$relativeParentPath = $userFolder->getRelativePath($file->getParent()->getPath());
				$transcriptPath = rtrim($relativeParentPath, '/') . '/' . $file->getName() . '.transcript';

				if ($userFolder->nodeExists($transcriptPath)) {
					$transcriptFile = $userFolder->get($transcriptPath);
				} else {
					$transcriptFile = $userFolder->newFile($transcriptPath);
				}

				$transcriptFile->putContent(file_get_contents($tmpZipPath));
				unlink($tmpZipPath);
			} else {
				unlink($tmpZipPath);
			}
		} catch (\Throwable $e) {
			$this->logger->error('PollTranscriptionJob: ' . $e->getMessage(), ['exception' => $e]);
			// On laisse le job en liste : il sera rejoué au prochain passage cron.
		}
	}
}
