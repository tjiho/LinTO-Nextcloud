<?php

declare(strict_types=1);

namespace OCA\LinTO\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Util;
use OCP\EventDispatcher\IEventDispatcher;
use OCA\Files\Event\LoadAdditionalScriptsEvent;

class Application extends App implements IBootstrap {
	public const APP_ID = 'linto';

	/** @psalm-suppress PossiblyUnusedMethod */
	public function __construct() {
		parent::__construct(self::APP_ID);

		$container = $this->getContainer();
        $eventDispatcher = $container->get(IEventDispatcher::class);
        $eventDispatcher->addListener(LoadAdditionalScriptsEvent::class, function() {
            Util::addInitScript(self::APP_ID, 'linto-fileActions');
        });
	}

	public function register(IRegistrationContext $context): void {
	    // $context->registerClass(\OCA\LinTO\Sections\LinTOAdmin::class, [\OCP\Settings\IIconSection::class]);
     //    $context->registerTaggedService(
     //        \OCA\LinTO\Sections\LinTOAdmin::class,
     //        'settings.section',
     //        ['id' => 'linto']
     //    );

     //    // 2. Settings form
     //    $context->registerClass(\OCA\LinTO\Settings\LinTOAdmin::class, [\OCP\Settings\ISettings::class]);
     //    $context->registerTaggedService(
     //        \OCA\LinTO\Settings\LinTOAdmin::class,
     //        'settings',
     //        ['section' => 'linto', 'type' => 'admin']
     //    );
     // $context->registerEventListener(LoadAdditionalScriptsEvent::class, function () {
     //     Util::addInitScript(self::APP_ID, 'linto-fileActions');
     // });
     // Util::addScript(self::APP_ID, 'linto-fileActions');
     //
	}

	public function boot(IBootContext $context): void {}
}
