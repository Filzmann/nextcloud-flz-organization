<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\AppInfo;

use OCA\FlzOrganization\BackgroundJob\RefreshHolidayCalendarJob;
use OCA\FlzOrganization\Calendar\CalendarContextSettingsService;
use OCA\FlzOrganization\Calendar\HolidayCalendarService;
use OCA\FlzOrganization\Controller\AdminController;
use OCA\FlzOrganization\Permission\PermissionProvider;
use OCA\FlzOrganization\Service\OrganizationAdminService;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\IAppContainer;
use OCP\AppFramework\Utility\ITimeFactory;

final class Application extends App implements IBootstrap {
    public const APP_ID = 'flz_organization';

    public function __construct(array $urlParams = []) {
        parent::__construct(self::APP_ID, $urlParams);
    }

    public function register(IRegistrationContext $context): void {
        // Register permission provider for the app
        $context->registerPermissionProvider(PermissionProvider::class);

        // Register service aliases for DI container
        $context->registerServiceAlias(
            OrganizationAdminService::class,
            function (IAppContainer $c) {
                return new OrganizationAdminService(
                    $c->get('OrganizationSettingsService'),
                    $c->get('CalendarContextSettingsService'),
                    $c->get('AuthorizationSettingsService'),
                );
            }
        );
    }

    public function boot(IBootContext $context): void {
        // No runtime bootstrap required
    }
}
