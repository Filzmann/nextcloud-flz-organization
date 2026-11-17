<?php

declare(strict_types=1);

if (getenv('FLZ_ORGANIZATION_COVERAGE_PROBE_CHILD') === '1')
{
    return;
}

$coverageFixture = sys_get_temp_dir() . '/flz-organization-coverage-' . bin2hex(random_bytes(8));
if (!mkdir($coverageFixture, 0700))
{
    throw new RuntimeException('Coverage contract fixture could not be created.');
}

try
{
    $coverageProbe = $coverageFixture . '/phpcov';
    $coverageLog = $coverageFixture . '/commands.jsonl';
    file_put_contents($coverageProbe, <<<'PHP'
#!/usr/bin/env php
<?php
file_put_contents(getenv('FLZ_ORGANIZATION_COVERAGE_PROBE_LOG'), json_encode(array_slice($argv, 1), JSON_THROW_ON_ERROR) . "\n", FILE_APPEND);
PHP);
    chmod($coverageProbe, 0700);
    $coverageEnvironment = array_merge(getenv(),
    [
        'PHP_COVERAGE_COMMAND' => $coverageProbe,
        'PHP_COVERAGE_OUTPUT_DIR' => $coverageFixture . '/reports',
        'FLZ_ORGANIZATION_COVERAGE_PROBE_LOG' => $coverageLog,
        'FLZ_ORGANIZATION_COVERAGE_PROBE_CHILD' => '1',
    ]);
    $runCoverageProbe = static function (array $environment) use ($coverageFixture): int
    {
        $process = proc_open(
            [PHP_BINARY, dirname(__DIR__) . '/run.php'],
            [
                0 => ['file', '/dev/null', 'r'],
                1 => ['file', $coverageFixture . '/stdout', 'w'],
                2 => ['file', $coverageFixture . '/stderr', 'w'],
            ],
            $pipes,
            null,
            $environment,
        );
        if (!is_resource($process))
        {
            throw new RuntimeException('Coverage contract subprocess could not start.');
        }
        return proc_close($process);
    };

    assertSame(0, $runCoverageProbe($coverageEnvironment), 'Configured coverage runner must succeed.');
    $coverageCommands = is_file($coverageLog)
        ? array_map(
            static fn (string $line): array => json_decode($line, true, flags: JSON_THROW_ON_ERROR),
            file($coverageLog, FILE_IGNORE_NEW_LINES),
        )
        : [];
    assertSame(2, count($coverageCommands), 'Coverage must instrument both the isolated contract and the main suite.');
    $coverageScripts = [];
    foreach ($coverageCommands as $coverageCommand)
    {
        assertSame('execute', $coverageCommand[0], 'Coverage must execute the test entrypoint.');
        assertTrue(in_array('--add-uncovered', $coverageCommand, true), 'Unexecuted application files must remain in the denominator.');
        $includePosition = array_search('--include', $coverageCommand, true);
        assertSame(dirname(__DIR__, 2) . '/lib', $coverageCommand[$includePosition + 1], 'Coverage must filter application runtime code.');
        $coverageScripts[] = file_get_contents($coverageCommand[array_key_last($coverageCommand)]);
    }
    assertTrue(str_contains($coverageScripts[0], '/Contract/OptionalPermissionRuntimeContractTest.php'), 'Isolated provider bootstrap must be measured separately.');
    assertTrue(str_contains($coverageScripts[1], '/suite.php'), 'Main application suite must be measured.');

    $coverageEnvironment['PHP_COVERAGE_COMMAND'] = $coverageFixture . '/missing-tool';
    assertTrue($runCoverageProbe($coverageEnvironment) !== 0, 'Missing configured coverage tooling must fail instead of silently running unmeasured tests.');
    assertSame(2, count(file($coverageLog)), 'Rejected coverage tooling must not execute test commands.');
}
finally
{
    $coverageEntries = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($coverageFixture, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );
    foreach ($coverageEntries as $coverageEntry)
    {
        $coverageEntry->isDir() ? rmdir($coverageEntry->getPathname()) : unlink($coverageEntry->getPathname());
    }
    rmdir($coverageFixture);
}
