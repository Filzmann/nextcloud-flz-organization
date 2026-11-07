<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\PublicApi\V1;

final class Contract {
    public const APP_ID = 'flz_organization';
    public const VERSION = '1.0';

    public static function supports(string $requestedVersion): bool {
        return $requestedVersion === self::VERSION;
    }
}

