<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\Tests\Service;

use OCA\FlzOrganization\Calendar\CalendarContext;
use OCA\FlzOrganization\Calendar\CalendarContextSettingsService;
use OCA\FlzOrganization\Organization\AuthorizationSettingsService;
use OCA\FlzOrganization\Organization\OrganizationDefinition;
use OCA\FlzOrganization\Organization\OrganizationSettingsService;
use OCA\FlzOrganization\Service\OrganizationAdminService;
use OCA\FlzOrganization\Tests\FakeAppConfig;

$config = new FakeAppConfig();

// Initialize settings services
$organizationSettings = new OrganizationSettingsService($config);
$calendarSettings = new CalendarContextSettingsService($config);
$authorizationSettings = new AuthorizationSettingsService($config, $organizationSettings);

// Create admin service
$adminService = new OrganizationAdminService($organizationSettings, $calendarSettings, $authorizationSettings);

// Test 1: Get default organization definition
$definition = $adminService->getOrganizationDefinition();
assertSame(OrganizationDefinition::defaults()->toArray(), $definition->toArray(), 
    'Admin service must return default organization definition when no config exists.');

// Test 2: Get default calendar context timezone name
$context = $adminService->getCalendarContext();
assertSame('Europe/Berlin', $context->toArray()['timezone'], 
    'Admin service must return default timezone (Europe/Berlin).');

// Test 3: Validate and save organization definition
// Use defaults and modify only the fields we need to test
$defaults = OrganizationDefinition::defaults()->toArray();
// Modify staffBlockLabel to test saving
$defaults['staffBlockLabel'] = 'Mitarbeiter';

$savedDefinition = $adminService->saveOrganizationDefinition($defaults);
assertTrue($savedDefinition['success'], 'Saving valid organization definition must succeed.');
assertSame('Mitarbeiter', $savedDefinition['definition']->toArray()['staffBlockLabel'], 
    'Saved definition must contain the modified staffBlockLabel.');

// Test 4: Validate invalid organization definition (duplicate role group IDs)
$invalidData = $defaults;
$invalidData['roles'] = [
    'office' => ['groupId' => 'same-id', 'label' => 'First', 'order' => 10],
    'another' => ['groupId' => 'same-id', 'label' => 'Second', 'order' => 20],
];
$invalidResult = $adminService->validateOrganizationDefinition($invalidData);
assertTrue(!($invalidResult['valid']), 'Duplicate role group IDs must be rejected.');
assertSame(1, count($invalidResult['errors']), 'Validation must report exactly one error for duplicate group IDs.');
assertSame('Die Gruppen-ID same-id ist mehrfach vergeben.', $invalidResult['errors'][0], 
    'Error message must correctly identify the duplicate group ID.');

// Test 5: Validate and save calendar context
$calendarResult = $adminService->saveCalendarContext([
    'version' => CalendarContext::VERSION,
    'countryCode' => 'DE',
    'subdivisionCode' => 'DE-BY',
    'timezone' => 'Europe/Berlin',
]);
assertTrue($calendarResult['success'], 'Saving valid calendar context must succeed.');
assertSame('DE-BY', $calendarResult['context']->subdivisionCode(), 
    'Saved calendar context must contain the provided subdivision code.');

// Test 6: Validate invalid calendar context
$invalidCalendar = $adminService->validateCalendarContext([
    'version' => CalendarContext::VERSION,
    'countryCode' => 'INVALID',
    'subdivisionCode' => 'DE-BY',
    'timezone' => 'Europe/Berlin',
]);
assertTrue(!($invalidCalendar['valid']), 'Invalid country code must be rejected.');

// Test 7: Get configuration status
$status = $adminService->getConfigurationStatus();
assertSame(true, array_key_exists('organization', $status), 'Status must include organization info.');
assertSame(true, array_key_exists('calendar', $status), 'Status must include calendar info.');
assertSame(true, array_key_exists('authorization', $status), 'Status must include authorization info.');
assertSame(true, array_key_exists('valid', $status), 'Status must include overall valid flag.');

// Test 8: Verify organization state after save
$orgState = $organizationSettings->state();
assertTrue($orgState['valid'], 'Organization definition must be valid after saving.');
assertTrue($orgState['persisted'], 'Organization definition must be marked as persisted.');

// Test 9: Verify calendar context in status
assertSame('DE-BY', $status['calendar']['subdivisionCode'], 
    'Status calendar subdivision must match saved context.');

// Test 10: Verify getAuthorizationSettings returns array
$authSettings = $adminService->getAuthorizationSettings();
assertSame(true, is_array($authSettings), 'Authorization settings must be an array.');

// Test 11: Verify defaults match after fresh instance
$adminService2 = new OrganizationAdminService($organizationSettings, $calendarSettings, $authorizationSettings);
$definition2 = $adminService2->getOrganizationDefinition();
assertSame('Mitarbeiter', $definition2->toArray()['staffBlockLabel'], 
    'Modified staffBlockLabel must persist across service instances.');

