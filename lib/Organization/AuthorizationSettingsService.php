<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\Organization;

use OCA\FlzOrganization\AppInfo\Application;
use OCP\IAppConfig;

/** Persists semantic peer scopes and resolves them against the current canonical organization. */
final class AuthorizationSettingsService {
    public const CONFIG_KEY = 'authorization_settings';
    private const CALENDAR_PEER_DEFAULT_ROLES = ['office', 'eb', 'pfk'];

    public function __construct(
        private IAppConfig $config,
        private OrganizationSettingsService $organization,
    ) {}

    /** @return array<string,bool> */
    public function calendarPeerEditing(): array { return $this->roleValues('calendarPeerEditing'); }

    /** @return array<string,bool> */
    public function vacationPeerEditing(): array { return $this->roleValues('vacationPeerEditing'); }

    /** @return array<string,bool> */
    public function vacationPeerApproval(): array { return $this->roleValues('vacationPeerApproval'); }

    public function isValid(): bool { return $this->data() !== null; }

    /** @return array<string,bool> */
    public function saveCalendarPeerEditing(array $values): array {
        return $this->saveRoleValues('calendarPeerEditing', $values);
    }

    /** @return array<string,bool> */
    public function saveVacationPeerEditing(array $values): array {
        return $this->saveRoleValues('vacationPeerEditing', $values);
    }

    /** @return array<string,bool> */
    public function saveVacationPeerApproval(array $values): array {
        return $this->saveRoleValues('vacationPeerApproval', $values);
    }

    /** @return array<string,bool> */
    private function saveRoleValues(string $key, array $values): array {
        $data = $this->data();
        if ($data === null) {
            $data = [];
        }
        $data[$key] = $this->semanticRoleValues($values);
        $data['version'] = 1;
        $this->config->setValueString(
            Application::APP_ID,
            self::CONFIG_KEY,
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
        );
        return $this->roleValues($key);
    }

    /** @return array<string,bool> */
    private function roleValues(string $key): array {
        $data = $this->data();
        $stored = $data[$key] ?? [];
        if (!is_array($stored)) {
            return [];
        }
        $result = [];
        foreach ($this->peerRoles() as $roleKey => $role) {
            $result[$role['groupId']] = $data !== null && array_key_exists($roleKey, $stored)
                ? (bool)$stored[$roleKey]
                : $data !== null && $key === 'calendarPeerEditing' && in_array($roleKey, self::CALENDAR_PEER_DEFAULT_ROLES, true);
        }
        return $result;
    }

    /** @return array<string,bool> */
    private function semanticRoleValues(array $values): array {
        $result = [];
        foreach ($this->peerRoles() as $roleKey => $role) {
            $result[$roleKey] = filter_var($values[$role['groupId']] ?? false, FILTER_VALIDATE_BOOL);
        }
        return $result;
    }

    /** @return array<string,array<string,mixed>> */
    private function peerRoles(): array {
        return array_filter($this->organization->definition()->roles(), static fn(array $role): bool => $role['peerEnabled']);
    }

    /** @return array<string,mixed>|null */
    private function data(): ?array {
        $raw = $this->config->getValueString(Application::APP_ID, self::CONFIG_KEY, '');
        if ($raw === '') {
            return [];
        }
        try {
            $data = json_decode($raw, true, 16, JSON_THROW_ON_ERROR);
            if (!is_array($data) || !$this->hasValidSchema($data)) {
                return null;
            }
            return $data;
        } catch (\Throwable) {
            return null;
        }
    }

    /** @param array<string,mixed> $data */
    private function hasValidSchema(array $data): bool {
        if (array_key_exists('version', $data) && $data['version'] !== 1) {
            return false;
        }
        foreach (['calendarPeerEditing', 'vacationPeerEditing', 'vacationPeerApproval'] as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }
            if (!is_array($data[$key])) {
                return false;
            }
            foreach ($data[$key] as $roleKey => $enabled) {
                if (!is_string($roleKey) || trim($roleKey) === '' || !is_bool($enabled)) {
                    return false;
                }
            }
        }
        return true;
    }
}

