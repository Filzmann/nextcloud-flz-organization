<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\Organization;

/**
 * Zweck: Bewertet die konfigurierbare, transitive FLZ-Weisungshierarchie für lokale Fachapps.
 * Zusammenspiel: SettingsService -> Definition -> Hierarchy -> PermissionPolicy.
 * Vertrag: Bereichsgebundene Leitungsrechte werden erst in der PermissionPolicy auf gemeinsame Bereiche begrenzt.
 */
class OrganizationHierarchy {
    protected OrganizationDefinition $definition;

    public function __construct(?OrganizationSettingsService $settings = null, ?OrganizationDefinition $definition = null) {
        $this->definition = $settings?->definition() ?? $definition ?? OrganizationDefinition::defaults();
    }

    public function definition(): OrganizationDefinition { return $this->definition; }

    public function manages(array $actorGroups, array $targetGroups): bool {
        foreach ($this->definition->roleKeysForGroups($actorGroups) as $actorRole) {
            foreach ($this->definition->roleKeysForGroups($targetGroups) as $targetRole) {
                if ($this->definition->managesRole($actorRole, $targetRole)) return true;
            }
        }
        return false;
    }

    public function managementRequiresSharedArea(array $actorGroups, array $targetGroups): bool {
        $matched = false;
        foreach ($this->definition->roleKeysForGroups($actorGroups) as $actorRole) {
            foreach ($this->definition->roleKeysForGroups($targetGroups) as $targetRole) {
                if (!$this->definition->managesRole($actorRole, $targetRole)) continue;
                $matched = true;
                if (!$this->definition->roleManagementIsAreaScoped($actorRole)) return false;
            }
        }
        return $matched;
    }

    public function targetIsSuperior(array $actorGroups, array $targetGroups): bool {
        return $this->manages($targetGroups, $actorGroups) && !$this->manages($actorGroups, $targetGroups);
    }
}
