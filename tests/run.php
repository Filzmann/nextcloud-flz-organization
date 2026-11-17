<?php

declare(strict_types=1);

use OCA\LocalBase\Tests\Support\PhpTestRunner;

require_once __DIR__ . '/bootstrap.php';

$entrypoints =
[
    __DIR__ . '/Contract/OptionalPermissionRuntimeContractTest.php',
    __DIR__ . '/suite.php',
];

foreach ($entrypoints as $entrypoint)
{
    $command = PhpTestRunner::withOptionalCoverage(dirname(__DIR__), [PHP_BINARY, $entrypoint]);
    passthru(implode(' ', array_map('escapeshellarg', $command)), $exitCode);
    if ($exitCode !== 0)
    {
        exit($exitCode);
    }
}

fwrite(STDOUT, "OK (isolated contract and main suite)\n");

