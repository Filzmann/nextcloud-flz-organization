<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\AppInfo;

use OCP\AppFramework\App;

final class Application extends App {
    public const APP_ID = 'flz_organization';

    public function __construct() {
        parent::__construct(self::APP_ID);
    }
}

