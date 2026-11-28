<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void
{
    if ($class === 'OCA\\LocalBase\\Tests\\Support\\PhpTestRunner')
    {
        // Development tooling only; application runtime remains independent.
        $runner = dirname(__DIR__, 2) . '/localbase/tests/Support/PhpTestRunner.php';
        if (!is_file($runner))
        {
            throw new RuntimeException('The development-only LocalBase test runner is missing beside this repository.');
        }
        require_once $runner;
        return;
    }
    $stubPrefix = 'OCA\\FlzDataProtection\\';
    if (str_starts_with($class, $stubPrefix)) {
        $stubFile = dirname(__DIR__) . '/tests/stubs/FlzDataProtection/' . str_replace('\\', '/', substr($class, strlen($stubPrefix))) . '.php';
        if (is_file($stubFile)) {
            require_once $stubFile;
        }
        return;
    }
    $prefix = 'OCA\\FlzOrganization\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $path = dirname(__DIR__) . '/lib/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($path)) {
        require_once $path;
    }
});

function assertTrue(bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function assertSame(mixed $expected, mixed $actual, string $message): void {
    if ($expected !== $actual) {
        throw new RuntimeException($message . '\nExpected: ' . var_export($expected, true) . '\nActual: ' . var_export($actual, true));
    }
}

function assertThrows(callable $callback, string $exceptionClass, string $message): void {
    try {
        $callback();
    } catch (Throwable $error) {
        if ($error instanceof $exceptionClass) {
            return;
        }
        throw new RuntimeException($message . ': unexpected ' . $error::class, 0, $error);
    }
    throw new RuntimeException($message . ': no exception thrown');
}

