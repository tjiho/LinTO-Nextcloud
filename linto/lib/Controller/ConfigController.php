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
use OCP\PreConditionNotMetException;

class ConfigController extends Controller {

	public function __construct(
		string   $appName,
		IRequest $request,
		private IConfig $config,
		private ?string $userId
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @param array $values
	 * @return DataResponse
	 * @throws PreConditionNotMetException
	 */

	#[FrontpageRoute(verb: 'POST', url: '/config')]
	public function setConfig(array $values): DataResponse {
		foreach ($values as $key => $value) {
		    $this->config->setAppValue(Application::APP_ID, $key, $value);
		}
		return new DataResponse([]);
	}
}
