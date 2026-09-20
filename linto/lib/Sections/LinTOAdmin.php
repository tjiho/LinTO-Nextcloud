<?php
namespace OCA\LinTO\Sections;

use OCA\LinTO\AppInfo\Application;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class LinTOAdmin implements IIconSection {
    private IL10N $l;
    private IURLGenerator $urlGenerator;

    public function __construct(IL10N $l, IURLGenerator $urlGenerator) {
        $this->l = $l;
        $this->urlGenerator = $urlGenerator;
    }

    public function getIcon(): string {
        // The settings sidebar sits on a light background, so it wants the dark
        // variant of the app icon.
        return $this->urlGenerator->imagePath(Application::APP_ID, 'app-dark.svg');
    }

    public function getID(): string {
        return 'linto';
    }

    public function getName(): string {
        return $this->l->t('LinTO');
    }

    public function getPriority(): int {
        return 98;
    }
}
