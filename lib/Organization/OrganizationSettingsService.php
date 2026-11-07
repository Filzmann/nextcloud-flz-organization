<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\Organization;

use OCA\FlzOrganization\AppInfo\Application;
use OCP\IAppConfig;

/** Owns the canonical organization definition in this app's native AppConfig. */
final class OrganizationSettingsService {
    public const CONFIG_KEY = 'organization_definition';

    public function __construct(private IAppConfig $config) {}

    public function definition(): OrganizationDefinition {
        return $this->state()['definition'];
    }

    /** @return array{definition:OrganizationDefinition,valid:bool,persisted:bool} */
    public function state(): array {
        $raw = $this->config->getValueString(Application::APP_ID, self::CONFIG_KEY, '');
        if ($raw === '') {
            return ['definition' => OrganizationDefinition::defaults(), 'valid' => false, 'persisted' => false];
        }
        try {
            $data = json_decode($raw, true, 64, JSON_THROW_ON_ERROR);
            return [
                'definition' => OrganizationDefinition::get(is_array($data) ? $data : []),
                'valid' => true,
                'persisted' => true,
            ];
        } catch (\Throwable) {
            return ['definition' => OrganizationDefinition::defaults(), 'valid' => false, 'persisted' => true];
        }
    }

    public function save(array $data): OrganizationDefinition {
        $definition = OrganizationDefinition::get($data);
        $this->config->setValueString(
            Application::APP_ID,
            self::CONFIG_KEY,
            json_encode($definition->toArray(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
        );
        return $definition;
    }
}

