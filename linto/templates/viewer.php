<?php

declare(strict_types=1);

use OCP\Util;

Util::addTranslations(OCA\LinTO\AppInfo\Application::APP_ID);
Util::addScript(OCA\LinTO\AppInfo\Application::APP_ID, OCA\LinTO\AppInfo\Application::APP_ID . '-viewer');
Util::addStyle(OCA\LinTO\AppInfo\Application::APP_ID, OCA\LinTO\AppInfo\Application::APP_ID . '-viewer');
?>

<div id="linto-viewer">

</div>
