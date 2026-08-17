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
use OCP\AppFramework\Services\IInitialState;

class TranscriptController extends Controller {

	public function __construct(
		string   $appName,
		IRequest $request,
		private IConfig $config,
		private IRootFolder $rootFolder,
		private ?string $userId,
		private IInitialState $initialState,
	) {
		parent::__construct($appName, $request);
	}


	#[NoCSRFRequired]
	#[NoAdminRequired]
	#[OpenAPI(OpenAPI::SCOPE_IGNORE)]
	#[FrontpageRoute(verb: 'GET', url: '/view/{fileId}')]
	public function setConfig(int $fileId): TemplateResponse {
		$nodes = $this->rootFolder->getById($fileId);

		if (empty($nodes)) {
			throw new NotFoundException('File not found');
		}

		$node = $nodes[0];
		$content = $node->getContent();

		$this->initialState->provideInitialState('content', [
			'fileId' => $fileId,
			'transcript' => $content,
			'fileName' => $node->getName(),
			'readOnly' => true,
		]);

		return new TemplateResponse(
			'linto',
			'viewer',
		);

    	// return new TemplateResponse(
    	// 	Application::APP_ID,
    	// 	'index',
    	// );
	}
}

// class TranscriptController extends Controller {
//     public function __construct(
// 		string $appName,
// 		IRequest $request,
// 		private IConfig $config,
// 		private IUserSession $userSession,
// 		private IRootFolder $rootFolder,
// 		private IClientService $clientService,
// 		private TranscribeJobMapper $transcribeJobMapper,
// 		private IJobList $jobList,
// 		private IDBConnection $db,
// 		private LoggerInterface $logger,
// 		private ?string $userId,
// 	) {
// 		parent::__construct($appName, $request);
// 	}

// 	#[NoAdminRequired]
// 	#[NoCSRFRequired]
// 	#[Route('/view/{fileId}')]
// 	public function view(int $fileId): TemplateResponse {
// 		$nodes = $this->rootFolder->getById($fileId);

// 		if (empty($nodes)) {
// 			throw new NotFoundException('File not found');
// 		}

// 		$node = $nodes[0];
// 		$content = $node->getContent();

// 		return new TemplateResponse(
// 			'linto',
// 			'viewer',
// 			[
// 				'fileId' => $fileId,
// 				'transcript' => $content,
// 				'fileName' => $node->getName(),
// 				'readOnly' => true,
// 			]
// 		);
// 	}

// 	#[NoAdminRequired]
// 	#[NoCSRFRequired]
// 	#[Route('/api/transcript/{fileId}')]
// 	public function get(int $fileId): JSONResponse {
// 		$nodes = $this->rootFolder->getById($fileId);

// 		if (empty($nodes)) {
// 			return new JSONResponse(['error' => 'File not found'], Http::STATUS_NOT_FOUND);
// 		}

// 		$node = $nodes[0];
// 		$content = $node->getContent();

// 		return new JSONResponse([
// 			'transcript' => json_decode($content, true),
// 			'fileName' => $node->getName(),
// 		]);
// 	}
// }
