<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\Permission;

use OCA\FlzPermissionMatrix\PublicApi\V1\PermissionProvider as PermissionProviderContract;
use OCA\FlzPermissionMatrix\PublicApi\V1\PermissionProviderDescriptor;
use OCA\FlzPermissionMatrix\PublicApi\V1\PermissionProviderResult;

/** Documents the deliberately empty app-owned permission inventory. */
final class PermissionProvider implements PermissionProviderContract
{
    public function descriptor(): PermissionProviderDescriptor
    {
        return new PermissionProviderDescriptor(
            'flz_organization',
            'Organisationsdienste',
            '1.0',
            ['permissions'],
        );
    }

    public function collect(): PermissionProviderResult
    {
        return new PermissionProviderResult([]);
    }
}
