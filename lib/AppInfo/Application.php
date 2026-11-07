<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\AppInfo;

use OCA\FlzOrganization\Controller\AdminController;
use OCA\FlzOrganization\Service\OrganizationAdminService;
use OCP\AppFramework\App;
use OCP\AppFramework\IAppContainer;

final class Application extends App {
    public const APP_ID = 'flz_organization';

    public function __construct() {
        parent::__construct(self::APP_ID);
    }

    /**
     * Register services in the DI container.
     */
    public function registerServices(IAppContainer $container): void {
        // Register AdminService with its dependencies
        $container->registerService(
            OrganizationAdminService::class,
            function (IAppContainer $c) {
                return new OrganizationAdminService(
                    $c->get('OrganizationSettingsService'),
                    $c->get('CalendarContextSettingsService'),
                    $c->get('AuthorizationSettingsService'),
                );
            }
        );

        // Register AdminController
        $container->registerService(
            AdminController::class,
            function (IAppContainer $c) {
                return new AdminController(
                    $c->get('Request'),
                    $c->get(OrganizationAdminService::class),
                );
            }
        );
    }
}
