<?php

declare(strict_types=1);

require_once __DIR__ . '/stubs.php';
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/Fakes.php';

$tests =
[
    __DIR__ . '/Contract/PhpCoverageRunnerContractTest.php',
    __DIR__ . '/Contract/PublicApiV1ContractTest.php',
    __DIR__ . '/Contract/AdminRouteSecurityContractTest.php',
    __DIR__ . '/Organization/OrganizationDefinitionContractTest.php',
    __DIR__ . '/Service/OrganizationRuntimeTest.php',
    __DIR__ . '/Service/CalendarRuntimeTest.php',
    __DIR__ . '/Service/OrganizationAdminServiceTest.php',
    __DIR__ . '/Service/AssistantContextListenerTest.php',
    __DIR__ . '/Integration/ApplicationIntegrationTest.php',
    __DIR__ . '/Permission/PermissionProviderTest.php',
    __DIR__ . '/Controller/AdminControllerTest.php',
    __DIR__ . '/BackgroundJob/RefreshHolidayCalendarJobTest.php',
];

foreach ($tests as $test)
{
    require $test;
}

fwrite(STDOUT, sprintf("OK (%d main-suite test files)\n", count($tests)));
