<?php

declare(strict_types=1);

use OCA\FlzOrganization\AppInfo\Application;
use OCA\FlzOrganization\Organization\AuthorizationSettingsService;
use OCA\FlzOrganization\Organization\OrganizationDefinition;
use OCA\FlzOrganization\Organization\OrganizationSettingsService;
use OCA\FlzOrganization\PublicApi\V1\OrganizationAuthorizationService;
use OCA\FlzOrganization\PublicApi\V1\OrganizationSnapshotService;
use OCA\FlzOrganization\Tests\FakeAppConfig;

$config = new FakeAppConfig();
$settings = new OrganizationSettingsService($config);
$authorizations = new AuthorizationSettingsService($config, $settings);
$snapshotService = new OrganizationSnapshotService($settings);
$authorizationService = new OrganizationAuthorizationService($settings, $authorizations);

assertTrue(!$settings->state()['valid'], 'A fresh install must not grant rights from unconfirmed defaults.');
assertTrue(!$snapshotService->snapshot()->isValid(), 'The public snapshot must fail closed before explicit configuration.');
assertTrue(!$authorizationService->canManage('actor', ['Buero', 'Bereich-West'], 'target', ['Buero', 'Bereich-West'], OrganizationAuthorizationService::SCOPE_CALENDAR_EDIT), 'An unconfigured provider must deny peer authorization.');

$definition = $settings->save(OrganizationDefinition::defaults()->toArray());
$snapshot = $snapshotService->snapshot();
assertTrue($snapshot->isValid(), 'An explicitly persisted canonical definition must become available.');
assertSame($definition->roles()['office']['groupId'], $snapshot->roleGroupId('office'), 'Snapshots must expose semantic role mappings.');
assertSame('BR-Mitglieder', $snapshot->identityGroupId('works_council_member'), 'Snapshots must expose the canonical works-council membership identity.');
assertSame($definition->toArray(), $snapshot->definition(), 'The public snapshot must expose the complete validated definition needed by calendar consumers.');
assertSame([$definition->roles()['office']['groupId'], $definition->roles()['eb']['groupId'], $definition->roles()['pfk']['groupId']], $authorizationService->peerGroups(OrganizationAuthorizationService::SCOPE_CALENDAR_EDIT), 'The public authorization service must expose only enabled calendar peer groups.');

$office = $definition->roles()['office']['groupId'];
$west = $definition->areas()['west']['groupId'];
assertTrue($authorizationService->canManage('actor', [$office, $west], 'target', [$office, $west], OrganizationAuthorizationService::SCOPE_CALENDAR_EDIT), 'Configured calendar peer editing must permit peers in the same area.');
assertTrue(!$authorizationService->canManage('actor', [$office, $west], 'target', [$office, $west], OrganizationAuthorizationService::SCOPE_VACATION_APPROVE), 'Vacation approval must remain disabled until explicitly configured.');
assertTrue($authorizationService->canManage('same', [], 'same', [], OrganizationAuthorizationService::SCOPE_VACATION_EDIT), 'Self-service remains an explicit scope rule after valid configuration.');

$config->inject(Application::APP_ID, OrganizationSettingsService::CONFIG_KEY, '{broken');
assertTrue(!$snapshotService->snapshot()->isValid(), 'Corrupt organization configuration must fail closed.');
assertTrue(!$authorizationService->canManage('same', [], 'same', [], OrganizationAuthorizationService::SCOPE_VACATION_EDIT), 'Corrupt configuration must deny even otherwise valid scope checks.');

