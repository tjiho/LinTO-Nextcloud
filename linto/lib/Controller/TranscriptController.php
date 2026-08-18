<?php

declare(strict_types=1);

namespace OCA\LinTO\Controller;

use OCA\LinTO\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\IRequest;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\Route;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\OpenAPI;
use OCP\IConfig;
use ZipArchive;
use OCP\AppFramework\Services\IInitialState;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\NotFoundResponse;
use OCP\AppFramework\Http\Response;
use OCP\Files\File;
use Psr\Log\LoggerInterface;

class TranscriptController extends Controller {

	public function __construct(
		string   $appName,
		IRequest $request,
		private IConfig $config,
		private IRootFolder $rootFolder,
		private ?string $userId,
		private IInitialState $initialState,
		private LoggerInterface $logger,
	) {
		parent::__construct($appName, $request);
	}


	#[NoCSRFRequired]
	#[NoAdminRequired]
	#[OpenAPI(OpenAPI::SCOPE_IGNORE)]
	#[FrontpageRoute(verb: 'GET', url: '/view/{fileId}')]
	public function setConfig(int $fileId): TemplateResponse {
	    $nodes = $this->rootFolder->getUserFolder($this->userId)->getById($fileId);

		if (empty($nodes)) {
			throw new NotFoundException('File not found');
		}

		$node = $nodes[0];
		$rawContent = $node->getContent();
		$transcriptContent = $this->extractTranscriptFromZip($rawContent);

		$this->initialState->provideInitialState('content', [
			'fileId' => $fileId,
			'transcript' => $transcriptContent,
			'fileName' => $node->getName(),
			'readOnly' => true,
		]);

		return new TemplateResponse(
			'linto',
			'viewer',
		);
	}

	/**
	 * Extract transcript.json from ZIP or return raw content if not a ZIP.
	 */
	private function extractTranscriptFromZip(string $content): string {
		// Try to open as ZIP
		$zip = new ZipArchive();
		$tmpPath = tempnam(sys_get_temp_dir(), 'linto_extract_');
		file_put_contents($tmpPath, $content);

		if ($zip->open($tmpPath) === true) {
			$transcriptJson = $zip->getFromName('transcript.json');
			$zip->close();
			unlink($tmpPath);
			return $transcriptJson ?: $content; // Fallback to raw if transcript.json missing
		}

		// Not a ZIP, assume legacy JSON format
		unlink($tmpPath);
		return $content;
	}

	#[NoAdminRequired]
	#[NoCSRFRequired]
	#[OpenAPI(OpenAPI::SCOPE_IGNORE)]
	#[FrontpageRoute(verb: 'GET', url: '/api/audio/{fileId}')]
	public function getAudio(int $fileId): Response {
        $nodes = $this->rootFolder->getUserFolder($this->userId)->getById($fileId);
        $node = $nodes[0] ?? null;

        if (!$node instanceof File) {
            return new DataDisplayResponse('E1-no-node', 404, ['Content-Type' => 'text/plain']);
        }

        $audioContent = $this->extractAudioFromZip($node->getContent());
        if ($audioContent === null) {
            return new DataDisplayResponse('E2-no-audio', 404, ['Content-Type' => 'text/plain']);
        }

        // $this->logger->warning('audio', [
        //     'len'   => strlen($audioContent),
        //     'magic' => bin2hex(substr($audioContent, 0, 4)),
        // ]);

        return new DataDisplayResponse(
            $audioContent,
            Http::STATUS_OK,
            ['Content-Type' => 'audio/mpeg']
        );
	}

	/**
	 * Extract audio.mp3 from ZIP or return null if not found.
	 */
	private function extractAudioFromZip(string $content): ?string {
		$zip = new ZipArchive();
		$tmpPath = tempnam(sys_get_temp_dir(), 'linto_extract_');
		file_put_contents($tmpPath, $content);

		if ($zip->open($tmpPath) === true) {
			$audioContent = $zip->getFromName('audio.mp3');
			$zip->close();
			unlink($tmpPath);
			return $audioContent ?: null;
		}

		// Not a ZIP
		unlink($tmpPath);
		return null;
	}
}
