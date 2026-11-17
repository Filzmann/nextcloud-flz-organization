<?php

declare(strict_types=1);

use OCA\FlzPermissionMatrix\PublicApi\V1\RegisterPermissionProvidersEvent;

return [
    'providerRegistrations' => [
        // The app registers no PersonalDataProvider: it stores no app-owned
        // personal identifiers, so only the deliberately empty permission
        // inventory is published to the permission matrix.
        'flz_permission_matrix' => [
            RegisterPermissionProvidersEvent::class,
        ],
    ],
    'uiPath' => '/index.php/apps/flz_organization/admin',
    'preGrantUiStatuses' => [200],
    'postGrantUiStatuses' => [200],
    // Native Nextcloud administration intentionally grants no domain access:
    // the app owns no user-grantable app-local permission scope.
    'grantService' => null,
    'permissionProbe' => null,
    'apiSmokes' => [
        ['/index.php/apps/flz_organization/admin', [200]],
    ],
];
