<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\PublicApi\V1;

use InvalidArgumentException;
use OCA\FlzOrganization\Organization\AuthorizationSettingsService;
use OCA\FlzOrganization\Organization\OrganizationHierarchy;
use OCA\FlzOrganization\Organization\OrganizationPermissionPolicy;
use OCA\FlzOrganization\Organization\OrganizationSettingsService;

/** Read-only authorization projection; native admin status never grants an implicit bypass. */
final class OrganizationAuthorizationService {
    public const SCOPE_CALENDAR_EDIT = 'calendar.edit';
    public const SCOPE_VACATION_EDIT = 'vacation.edit';
    public const SCOPE_VACATION_APPROVE = 'vacation.approve';

    public function __construct(
        private OrganizationSettingsService $organization,
        private AuthorizationSettingsService $settings,
    ) {}

    /** @param list<string> $actorGroups @param list<string> $targetGroups */
    public function canManage(
        string $actorUid,
        array $actorGroups,
        string $targetUid,
        array $targetGroups,
        string $scope,
        bool $allowSelf = true,
    ): bool {
        $state = $this->organization->state();
        if (!$state['valid'] || !$this->settings->isValid()) {
            return false;
        }
        $peerGroups = $this->peerGroups($scope);
        $policy = new OrganizationPermissionPolicy(new OrganizationHierarchy(definition: $state['definition']));
        return $policy->canManage($actorUid, false, $actorGroups, $targetUid, $targetGroups, $peerGroups, $allowSelf);
    }

    /** @return list<string> */
    public function peerGroups(string $scope): array {
        if (!$this->organization->state()['valid'] || !$this->settings->isValid()) {
            return [];
        }
        return array_keys(array_filter(match ($scope) {
            self::SCOPE_CALENDAR_EDIT => $this->settings->calendarPeerEditing(),
            self::SCOPE_VACATION_EDIT => $this->settings->vacationPeerEditing(),
            self::SCOPE_VACATION_APPROVE => $this->settings->vacationPeerApproval(),
            default => throw new InvalidArgumentException('Unknown organization authorization scope.'),
        }));
    }
}

