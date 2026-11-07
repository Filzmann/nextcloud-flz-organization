<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\PublicApi\V1;

use InvalidArgumentException;

/** Datensparsamer, unveränderlicher V1-Vertrag für semantische Organisationsgruppen. */
final class OrganizationSnapshot {
    public const CONTRACT_VERSION = '1.0';

    /**
     * @param array<string, array{groupId: string, label: string}> $roles
     * @param array<string, array{groupId: string, label: string}> $areas
     * @param array<string, array{groupId: string, label: string}> $identityGroups
     * @param array<string, mixed> $definition
     */
    public function __construct(
        private bool $valid,
        private int $definitionVersion,
        private array $roles,
        private array $areas,
        private array $identityGroups = [],
        private array $definition = [],
    ) {
        if ($definitionVersion < 1) {
            throw new InvalidArgumentException('Invalid organization definition version.');
        }
        if (!$valid) {
            $this->roles = [];
            $this->areas = [];
            $this->identityGroups = [];
            $this->definition = [];
            return;
        }

        $this->roles = $this->normalizeMappings($roles);
        $this->areas = $this->normalizeMappings($areas);
        $this->identityGroups = $this->normalizeMappings($identityGroups);
        $groupIds = [
            ...array_column($this->roles, 'groupId'),
            ...array_column($this->areas, 'groupId'),
            ...array_column($this->identityGroups, 'groupId'),
        ];
        if (count($groupIds) !== count(array_unique($groupIds))) {
            throw new InvalidArgumentException('Organization group mappings must be unique.');
        }
        $this->definition = $this->normalizeDefinition($definition);
    }

    public function contractVersion(): string { return self::CONTRACT_VERSION; }
    public function isValid(): bool { return $this->valid; }
    public function definitionVersion(): int { return $this->definitionVersion; }

    /** @return array<string, array{groupId: string, label: string}> */
    public function roles(): array { return $this->roles; }

    /** @return array<string, array{groupId: string, label: string}> */
    public function areas(): array { return $this->areas; }

    /** @return array<string, array{groupId: string, label: string}> */
    public function identityGroups(): array { return $this->identityGroups; }

    /** @return array<string, mixed> */
    public function definition(): array { return $this->definition; }

    public function roleGroupId(string $roleKey): ?string { return $this->roles[$roleKey]['groupId'] ?? null; }
    public function areaGroupId(string $areaKey): ?string { return $this->areas[$areaKey]['groupId'] ?? null; }
    public function identityGroupId(string $identityKey): ?string { return $this->identityGroups[$identityKey]['groupId'] ?? null; }

    public function checksum(): string {
        return hash('sha256', json_encode($this->payload(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    /** @return array{contractVersion: string, valid: bool, definitionVersion: int, checksum: string, roles: array<string, array{groupId: string, label: string}>, areas: array<string, array{groupId: string, label: string}>, identityGroups: array<string, array{groupId: string, label: string}>, definition: array<string, mixed>} */
    public function toArray(): array {
        return [...$this->payload(), 'checksum' => $this->checksum()];
    }

    /** @return array{contractVersion: string, valid: bool, definitionVersion: int, roles: array<string, array{groupId: string, label: string}>, areas: array<string, array{groupId: string, label: string}>, identityGroups: array<string, array{groupId: string, label: string}>, definition: array<string, mixed>} */
    private function payload(): array {
        return [
            'contractVersion' => self::CONTRACT_VERSION,
            'valid' => $this->valid,
            'definitionVersion' => $this->definitionVersion,
            'roles' => $this->roles,
            'areas' => $this->areas,
            'identityGroups' => $this->identityGroups,
            'definition' => $this->definition,
        ];
    }

    /**
     * @param array<mixed> $mappings
     * @return array<string, array{groupId: string, label: string}>
     */
    private function normalizeMappings(array $mappings): array {
        $normalized = [];
        foreach ($mappings as $key => $mapping) {
            if (!is_string($key) || trim($key) === '' || !is_array($mapping)) {
                throw new InvalidArgumentException('Invalid organization mapping identity.');
            }
            $groupId = $mapping['groupId'] ?? null;
            $label = $mapping['label'] ?? null;
            if (!is_string($groupId) || trim($groupId) === '' || !is_string($label) || trim($label) === '') {
                throw new InvalidArgumentException('Invalid organization mapping fields.');
            }
            $normalized[trim($key)] = ['groupId' => trim($groupId), 'label' => trim($label)];
        }
        return $normalized;
    }

    /** @param array<string, mixed> $definition @return array<string, mixed> */
    private function normalizeDefinition(array $definition): array {
        if ($definition === []) {
            return [];
        }
        if (($definition['version'] ?? null) !== $this->definitionVersion
            || !is_string($definition['staffBlockLabel'] ?? null)
            || trim($definition['staffBlockLabel']) === ''
            || !is_array($definition['roles'] ?? null)
            || !is_array($definition['areas'] ?? null)
            || !is_array($definition['hierarchy'] ?? null)) {
            throw new InvalidArgumentException('Invalid public organization definition.');
        }
        foreach ($this->roles as $key => $mapping) {
            $definitionRole = $definition['roles'][$key] ?? null;
            if (!is_array($definitionRole)
                || ($definitionRole['groupId'] ?? null) !== $mapping['groupId']
                || ($definitionRole['label'] ?? null) !== $mapping['label']) {
                throw new InvalidArgumentException('Public organization role projection is inconsistent.');
            }
        }
        foreach ($this->areas as $key => $mapping) {
            $definitionArea = $definition['areas'][$key] ?? null;
            if (!is_array($definitionArea)
                || ($definitionArea['groupId'] ?? null) !== $mapping['groupId']
                || ($definitionArea['label'] ?? null) !== $mapping['label']) {
                throw new InvalidArgumentException('Public organization area projection is inconsistent.');
            }
        }
        return $definition;
    }
}
