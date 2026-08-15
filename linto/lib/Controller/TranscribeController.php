<?php

declare(strict_types=1);

namespace OCA\LinTO\Controller;

use OCA\LinTO\AppInfo\Application;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\Files\IRootFolder;
use OCP\Files\File;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IClient;
use OCP\AppFramework\Http;

class TranscribeController extends Controller {

	private const LINTO_API_BASE = 'https://studio.linto.ai/cm-api/api';

	public function __construct(
		string $appName,
		IRequest $request,
		private IConfig $config,
		private IUserSession $userSession,
		private IRootFolder $rootFolder,
		private IClientService $clientService,
		private ?string $userId,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * Fetch available services from Linto API
	 */
	private function fetchServices(string $token): array|DataResponse {
		$client = $this->clientService->newClient();

		try {
			$response = $client->get(self::LINTO_API_BASE . '/services', [
				'headers' => [
					'Authorization' => 'Bearer ' . $token,
					'Content-Type' => 'application/json',
				],
			]);

			if ($response->getStatusCode() !== 200) {
				return new DataResponse(['error' => 'Failed to fetch services'], Http::STATUS_BAD_REQUEST);
			}

			$data = json_decode($response->getBody(), true);
			if ($data === null) {
				return new DataResponse(['error' => 'Invalid JSON response'], Http::STATUS_INTERNAL_SERVER_ERROR);
			}
			return $data;
		} catch (\Exception $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * Build service config from service data (port of generate_service_config.py)
	 * enable_punctuation and enable_diarization are hardcoded to true
	 */
	private function buildServiceConfig(array $service): array {
		if (empty($service)) {
			return [];
		}

		$languageValue = $service['language'] ?? '*';
		$isWhisper = ($service['model_type'] ?? null) === 'whisper';
		$subServices = $service['sub_services'] ?? [];

		$punctuationList = $subServices['punctuation'] ?? [];
		$punctuationService = null;
		if (!empty($punctuationList) && !$isWhisper) {
			$punctuationService = $punctuationList[0]['service_name'] ?? null;
		}

		$diarizationList = $subServices['diarization'] ?? [];
		$diarizationService = null;
		if (!empty($diarizationList)) {
			$diarizationService = $diarizationList[0]['service_name'] ?? null;
		}

		$diarizationEffective = !empty($diarizationService);

		$endpoint = $service['endpoints'][0]['endpoint'] ?? '';
		$endpoint = ltrim($endpoint, '/');

		return [
			'serviceName' => $service['serviceName'] ?? '',
			'endpoint' => $endpoint,
			'lang' => $languageValue,
			'config' => [
				'language' => $languageValue,
				'punctuationConfig' => [
					'enablePunctuation' => !$isWhisper,
					'serviceName' => $punctuationService,
				],
				'diarizationConfig' => [
					'enableDiarization' => $diarizationEffective,
					'numberOfSpeaker' => $diarizationEffective ? 0 : null,
					'maxNumberOfSpeaker' => $diarizationEffective ? 100 : null,
					'serviceName' => $diarizationService,
				],
				'enableNormalization' => true,
				'modelType' => $service['model_type'] ?? null,
				'vadConfig' => [
					'enableVAD' => true,
					'methodName' => 'WebRTC',
					'minDuration' => $isWhisper ? 30 : 0,
				],
			],
		];
	}

	/**
	 * Create transcription job on Linto API
	 */
	private function createTranscription(int $fileId, string $token, array $services): DataResponse {
		$userId = $this->userSession->getUser()->getUID();
		$userFolder = $this->rootFolder->getUserFolder($userId);

		$nodes = $userFolder->getById($fileId);
		if (empty($nodes)) {
			return new DataResponse(['error' => 'File not found'], Http::STATUS_NOT_FOUND);
		}

		$file = $nodes[0];
		if (!$file instanceof File || !str_starts_with($file->getMimeType(), 'audio/')) {
			return new DataResponse(['error' => 'Invalid file type'], Http::STATUS_BAD_REQUEST);
		}

		// Build config from first service
		$config = $this->buildServiceConfig($services[0]);

		$client = $this->clientService->newClient();

		try {
			$response = $client->post(self::LINTO_API_BASE . '/organizations/6785248086d6ffb5a431d0b2/conversations/create', [
				'headers' => [
					'Authorization' => 'Bearer ' . $token,
				],
				'multipart' => [
					['name' => 'name', 'contents' => 'Transcription from nextcloud'],
					['name' => 'file', 'contents' => $file->fopen('r'), 'filename' => $file->getName()],
					['name' => 'serviceName', 'contents' => $config['serviceName']],
					['name' => 'transcriptionConfig', 'contents' => json_encode($config['config'])],
					['name' => 'lang', 'contents' => '*'],
					['name' => 'endpoint', 'contents' => $config['endpoint']],
				],
			]);

			if ($response->getStatusCode() !== 200 && $response->getStatusCode() !== 201) {
				return new DataResponse(['error' => 'Transcription failed'], Http::STATUS_INTERNAL_SERVER_ERROR);
			}

			return new DataResponse(json_decode($response->getBody(), true));
		} catch (\Exception $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_INTERNAL_SERVER_ERROR);
		}
	}

	#[NoAdminRequired]
	#[FrontpageRoute(verb: 'POST', url: '/transcribe')]
	public function transcribe(int $fileId): DataResponse {
		// Get request token from app config
		$token = $this->config->getAppValue(Application::APP_ID, 'apiKey');
		if (empty($token)) {
			return new DataResponse(['error' => 'Request token not configured'], Http::STATUS_BAD_REQUEST);
		}

		// Step 1: Fetch available services
		$services = $this->fetchServices($token);
		if ($services instanceof DataResponse) {
			return $services;
		}

		// Step 2: Create transcription
		return $this->createTranscription($fileId, $token, $services);
	}
}
