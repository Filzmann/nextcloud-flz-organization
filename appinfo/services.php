<?php

declare(strict_types=1);

use OCA\FlzOrganization\BackgroundJob\RefreshHolidayCalendarJob;
use OCA\FlzOrganization\Calendar\CalendarContextSettingsService;
use OCA\FlzOrganization\Calendar\HolidayCalendarCacheStore;
use OCA\FlzOrganization\Calendar\HolidayCalendarService;
use OCA\FlzOrganization\Calendar\OpenHolidaysClient;
use OCA\FlzOrganization\Controller\AdminController;
use OCA\FlzOrganization\Organization\AuthorizationSettingsService;
use OCA\FlzOrganization\Organization\OrganizationSettingsService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Http\Client\IClientService;
use OCP\IAppConfig;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

// Service definitions for the DI container
return [
    // Server container services (pass-through)
    IAppConfig::class => function ($c) {
        return $c->getServerContainer()->get(IAppConfig::class);
    },
    ITimeFactory::class => function ($c) {
        return $c->getServerContainer()->get(ITimeFactory::class);
    },
    IClientService::class => function ($c) {
        return $c->getServerContainer()->get(IClientService::class);
    },
    LoggerInterface::class => function ($c) {
        return $c->getServerContainer()->get(LoggerInterface::class);
    },
    IRequest::class => function ($c) {
        return $c->getServerContainer()->get(IRequest::class);
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

    // Background job for daily holiday calendar refresh
    RefreshHolidayCalendarJob::class => function ($c) {
        return new RefreshHolidayCalendarJob(
            $c->get(ITimeFactory::class),
            $c->get(CalendarContextSettingsService::class),
            $c->get(HolidayCalendarService::class),
        );
    },

    // Admin services and controllers
    'OrganizationAdminService' => function ($c) {
        return new \OCA\FlzOrganization\Service\OrganizationAdminService(
            $c->get(OrganizationSettingsService::class),
            $c->get(CalendarContextSettingsService::class),
            $c->get(AuthorizationSettingsService::class),
            $c->getServerContainer()->getL10N('flz_organization'),
        );
    },

    AdminController::class => function ($c) {
        return new AdminController(
            $c->get(IRequest::class),
            $c->get('OrganizationAdminService'),
        );
    },
];
