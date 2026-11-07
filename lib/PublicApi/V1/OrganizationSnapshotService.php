<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\PublicApi\V1;

use OCA\FlzOrganization\Organization\OrganizationSettingsService;

final class OrganizationSnapshotService {
    public function __construct(private OrganizationSettingsService $settings) {}

    public function snapshot(): OrganizationSnapshot {
        $state = $this->settings->state();
        $definition = $state['definition'];
        $definitionVersion = (int)$definition->toArray()['version'];
        if (!$state['valid']) {
            return new OrganizationSnapshot(false, $definitionVersion, [], []);
        }
        $roles = [];
        foreach ($definition->roles() as $key => $role) {
            $roles[$key] = ['groupId' => $role['groupId'], 'label' => $role['label']];
        }
        $areas = [];
        foreach ($definition->areas() as $key => $area) {
            $areas[$key] = ['groupId' => $area['groupId'], 'label' => $area['label']];
        }
        $identityGroups = [];
        foreach ($definition->identityGroups() as $key => $identityGroup) {
            $identityGroups[$key] = ['groupId' => $identityGroup['groupId'], 'label' => $identityGroup['label']];
        }
        return new OrganizationSnapshot(true, $definitionVersion, $roles, $areas, $identityGroups, $definition->toArray());
    }
}

