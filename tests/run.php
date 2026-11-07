<?php

declare(strict_types=1);

require_once __DIR__ . '/stubs.php';
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/Fakes.php';

$tests = [
    __DIR__ . '/Contract/PublicApiV1ContractTest.php',
    __DIR__ . '/Service/OrganizationRuntimeTest.php',
    __DIR__ . '/Service/CalendarRuntimeTest.php',
];

foreach ($tests as $test) {
    require $test;
}

fwrite(STDOUT, sprintf("OK (%d test files)\n", count($tests)));

