<?php

declare(strict_types=1);

use OCA\FlzOrganization\AppInfo\Application;
use OCA\FlzOrganization\Calendar\CalendarContextSettingsService;
use OCA\FlzOrganization\Calendar\HolidayCalendarCacheStore;
use OCA\FlzOrganization\Calendar\HolidayCalendarService;
use OCA\FlzOrganization\Calendar\OpenHolidaysClient;
use OCA\FlzOrganization\Controller\AdminController;
use OCA\FlzOrganization\Organization\AuthorizationSettingsService;
use OCA\FlzOrganization\Organization\OrganizationSettingsService;
use OCA\FlzOrganization\Service\OrganizationAdminService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Http\Client\IClientService;
use OCP\IAppConfig;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

// Service definitions for the DI container
return [
    // App config
    IAppConfig::class => function ($c) {
        return $c->getServerContainer()->get(IAppConfig::class);
    },

    // Time factory
    ITimeFactory::class => function ($c) {
        return $c->getServerContainer()->get(ITimeFactory::class);
    },

    // Client service
    IClientService::class => function ($c) {
        return $c->getServerContainer()->get(IClientService::class);
    },

    // Logger
    LoggerInterface::class => function ($c) {
        return $c->getServerContainer()->get(LoggerInterface::class);
    },

    // App-specific services
    OrganizationSettingsService::class => function ($c) {
        return new OrganizationSettingsService(
            $c->get(IAppConfig::class),
        );
    },

    CalendarContextSettingsService::class => function ($c) {
        return new CalendarContextSettingsService(
            $c->get(IAppConfig::class),
        );
    },

    AuthorizationSettingsService::class => function ($c) {
        return new AuthorizationSettingsService(
            $c->get(IAppConfig::class),
            $c->get(OrganizationSettingsService::class),
        );
    },

    HolidayCalendarCacheStore::class => function ($c) {
        return new HolidayCalendarCacheStore(
            $c->get(IAppConfig::class),
        );
    },

    OpenHolidaysClient::class => function ($c) {
        return new OpenHolidaysClient(
            $c->get(IClientService::class),
        );
    },

    HolidayCalendarService::class => function ($c) {
        return new HolidayCalendarService(
            $c->get(OpenHolidaysClient::class),
            $c->get(HolidayCalendarCacheStore::class),
            $c->get(CalendarContextSettingsService::class),
            $c->get(ITimeFactory::class),
            $c->get(LoggerInterface::class),
        );
    },

    OrganizationAdminService::class => function ($c) {
        return new OrganizationAdminService(
            $c->get(OrganizationSettingsService::class),
            $c->get(CalendarContextSettingsService::class),
            $c->get(AuthorizationSettingsService::class),
        );
    },

    // Register AdminController
    AdminController::class => function ($c) {
        return new AdminController(
            $c->get(IRequest::class),
            $c->get(OrganizationAdminService::class),
        );
    },
];
