<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\AppInfo;

use OCA\FlzDataProtection\PublicApi\V1\RegisterProcessingMetadataProvidersEvent;
use OCA\FlzOrganization\Privacy\FlzOrganizationProcessingMetadataProviderListener;

use OCA\FlzKapazitaet\PublicApi\V1\AssistantContextQueryEvent;
use OCA\FlzOrganization\Listener\AssistantContextListener;
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
        $context->registerEventListener(RegisterProcessingMetadataProvidersEvent::class, FlzOrganizationProcessingMetadataProviderListener::class);
        $context->registerEventListener(
            RegisterPermissionProvidersEvent::class,
            PermissionProviderListener::class,
        );
        // Provider für Kontext-Hinweise (Team im Büro) des flz_kapazitaet-V1-Vertrags; ohne installierte App feuert das Event nie.
        $context->registerEventListener(
            AssistantContextQueryEvent::class,
            AssistantContextListener::class,
        );
    }

    public function boot(IBootContext $context): void
    {
    }
}
