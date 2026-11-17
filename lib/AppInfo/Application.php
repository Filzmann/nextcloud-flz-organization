<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\AppInfo;

use OCA\FlzOrganization\Permission\PermissionProviderListener;
use OCA\FlzPermissionMatrix\PublicApi\V1\RegisterPermissionProvidersEvent;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

final class Application extends App implements IBootstrap
{
    public const APP_ID = 'flz_organization';

    public function __construct(array $urlParams = [])
    {
        parent::__construct(self::APP_ID, $urlParams);
    }

    public function register(IRegistrationContext $context): void
    {
        $context->registerEventListener(
            RegisterPermissionProvidersEvent::class,
            PermissionProviderListener::class,
        );
    }

    public function boot(IBootContext $context): void
    {
    }
}
