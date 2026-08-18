<?php

declare(strict_types=1);

namespace OCA\LinTO\Notification;

use OCA\LinTO\AppInfo\Application;
use OCP\IURLGenerator;
use OCP\L10N\IFactory;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;
use OCP\Notification\UnknownNotificationException;

/**
 * Without a registered INotifier, OC\Notification\Manager::prepare() throws
 * IncompleteParsedNotificationException for any notification from this app
 * and it never reaches the bell — this is what actually turns a
 * notify()'d notification into visible text.
 */
class Notifier implements INotifier {
	public function __construct(
		private IFactory $l10nFactory,
		private IURLGenerator $urlGenerator,
	) {
	}

	public function getID(): string {
		return Application::APP_ID;
	}

	public function getName(): string {
		return $this->l10nFactory->get(Application::APP_ID)->t('LinTO');
	}

	public function prepare(INotification $notification, string $languageCode): INotification {
		if ($notification->getApp() !== Application::APP_ID) {
			throw new UnknownNotificationException();
		}

		$l = $this->l10nFactory->get(Application::APP_ID, $languageCode);

		switch ($notification->getSubject()) {
			case 'transcription_done':
				$notification->setParsedSubject($l->t('Transcription complete'))
					->setParsedMessage($l->t('Your file has been transcribed successfully'))
					->setIcon($this->urlGenerator->imagePath(Application::APP_ID, 'app.svg'));
				break;
			case 'transcription_failed':
				$notification->setParsedSubject($l->t('Transcription failed'))
					->setParsedMessage($l->t('The transcription of your file failed'))
					->setIcon($this->urlGenerator->imagePath(Application::APP_ID, 'app.svg'));
				break;
			default:
				throw new UnknownNotificationException();
		}

		return $notification;
	}
}
