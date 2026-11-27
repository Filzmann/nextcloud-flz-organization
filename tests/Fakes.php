<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\Tests;

use OCP\IAppConfig;
use OCP\IConfig;

final class FakeSystemConfig implements IConfig {
    /** @var array<string,string> */
    private array $values;

    public function __construct(array $values = ['timezone' => 'Europe/Berlin']) {
        $this->values = $values;
    }

    public function getSystemValueString(string $key, string $default = ''): string {
        return $this->values[$key] ?? $default;
    }
}

final class FakeAppConfig implements IAppConfig {
    /** @var array<string,string> */
    private array $values = [];

    public function getValueString(string $appId, string $key, string $default = '', bool $lazy = false): string {
        return $this->values[$appId . ':' . $key] ?? $default;
    }

    public function setValueString(string $appId, string $key, string $value, bool $lazy = false, int $sensitive = 0): bool {
        $this->values[$appId . ':' . $key] = $value;
        return true;
    }

    public function inject(string $appId, string $key, string $value): void {
        $this->values[$appId . ':' . $key] = $value;
    }
}

