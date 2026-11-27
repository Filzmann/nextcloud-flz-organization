<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\Tests\Controller;

use OCA\FlzOrganization\Calendar\CalendarContext;
use OCA\FlzOrganization\Calendar\CalendarContextSettingsService;
use OCA\FlzOrganization\Controller\AdminController;
use OCA\FlzOrganization\Organization\AuthorizationSettingsService;
use OCA\FlzOrganization\Organization\OrganizationDefinition;
use OCA\FlzOrganization\Organization\OrganizationSettingsService;
use OCA\FlzOrganization\Service\OrganizationAdminService;
use OCA\FlzOrganization\Tests\FakeAppConfig;
use OCA\FlzOrganization\Tests\FakeSystemConfig;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use ReflectionClass;

// Fachliche Invariante: Der AdminController bleibt dünn und liefert für jede
// Admin-Aktion eine TemplateResponse der eigenen App mit dem korrekten
// Template; Schreibaktionen bleiben ohne gültige Daten wirkungslos.
// Ebene: Controller-Test mit echtem OrganizationAdminService und FakeAppConfig.
// Beweist: Template-Auswahl, App-ID, Parameterübergabe, Persistenz bzw.
// deren Ausbleiben bei Validierungsfehlern. Beweist nicht: HTTP-Routing,
// CSRF- oder Admin-Prüfung (siehe Contract/AdminRouteSecurityContractTest).

$request = new class implements IRequest {
};

$config = new FakeAppConfig();
$organizationSettings = new OrganizationSettingsService($config);
$calendarSettings = new CalendarContextSettingsService($config, new FakeSystemConfig());
$authorizationSettings = new AuthorizationSettingsService($config, $organizationSettings);
$adminService = new OrganizationAdminService($organizationSettings, $calendarSettings, $authorizationSettings);
$controller = new AdminController($request, $adminService);

// Test 1: index liefert die Übersichtsseite der eigenen App mit Statusdaten.
$response = $controller->index();
assertTrue($response instanceof TemplateResponse, 'index() must return a TemplateResponse.');
assertSame('flz_organization', $response->getApp(), 'index() must render in the flz_organization app.');
assertSame('admin/index', $response->getTemplateName(), 'index() must use the admin/index template.');
assertTrue(isset($response->getParams()['status']['organization']), 'index() must pass the configuration status to the template.');

// Test 2: organizationForm liefert das Formular mit der persisted Definition.
$response = $controller->organizationForm();
assertSame('admin/organization-form', $response->getTemplateName(), 'organizationForm() must use the admin/organization-form template.');
assertSame(
    OrganizationDefinition::defaults()->toArray(),
    $response->getParams()['definition'],
    'organizationForm() must pass the current organization definition to the template.'
);

// Test 3: calendarForm liefert das Formular mit dem persisted Kontext.
$response = $controller->calendarForm();
assertSame('admin/calendar-form', $response->getTemplateName(), 'calendarForm() must use the admin/calendar-form template.');
assertSame('DE', $response->getParams()['context']['countryCode'], 'calendarForm() must pass the current calendar context to the template.');

// Test 4 (positiv): saveOrganization mit gültigen Daten speichert und meldet Erfolg.
$validData = OrganizationDefinition::defaults()->toArray();
$validData['staffBlockLabel'] = 'Mitarbeiter';
$response = $controller->saveOrganization($validData);
assertSame('admin/organization-form', $response->getTemplateName(), 'saveOrganization() must re-render the organization form.');
assertSame(true, $response->getParams()['saved'] ?? null, 'saveOrganization() must flag a successful save.');
assertSame('Mitarbeiter', $adminService->getOrganizationDefinition()->toArray()['staffBlockLabel'], 'A valid save must persist the definition.');

// Test 5 (negativ): saveOrganization mit ungültigen Daten ändert nichts.
$invalidData = OrganizationDefinition::defaults()->toArray();
$invalidData['roles'] = [
    'office' => ['groupId' => 'same-id', 'label' => 'First', 'order' => 10],
    'another' => ['groupId' => 'same-id', 'label' => 'Second', 'order' => 20],
];
$response = $controller->saveOrganization($invalidData);
assertSame('admin/organization-form', $response->getTemplateName(), 'A rejected save must re-render the organization form.');
assertTrue(($response->getParams()['errors'] ?? []) !== [], 'A rejected save must expose validation errors.');
assertTrue(!isset($response->getParams()['saved']), 'A rejected save must not be flagged as saved.');
assertSame('Mitarbeiter', $adminService->getOrganizationDefinition()->toArray()['staffBlockLabel'], 'A rejected save must not change the persisted definition.');

// Test 6 (positiv): saveCalendar mit gültigem Kontext speichert und meldet Erfolg.
$validCalendar = [
    'version' => CalendarContext::VERSION,
    'countryCode' => 'DE',
    'subdivisionCode' => 'DE-BY',
    'timezone' => 'Europe/Berlin',
];
$response = $controller->saveCalendar($validCalendar);
assertSame('admin/calendar-form', $response->getTemplateName(), 'saveCalendar() must re-render the calendar form.');
assertSame(true, $response->getParams()['saved'] ?? null, 'saveCalendar() must flag a successful save.');
assertSame('DE-BY', $adminService->getCalendarContext()->subdivisionCode(), 'A valid save must persist the calendar context.');

// Test 7 (negativ): saveCalendar mit ungültigem Kontext ändert nichts.
$invalidCalendar = $validCalendar;
$invalidCalendar['countryCode'] = 'INVALID';
$response = $controller->saveCalendar($invalidCalendar);
assertSame('admin/calendar-form', $response->getTemplateName(), 'A rejected save must re-render the calendar form.');
assertTrue(($response->getParams()['errors'] ?? []) !== [], 'A rejected calendar save must expose validation errors.');
assertTrue(!isset($response->getParams()['saved']), 'A rejected calendar save must not be flagged as saved.');
assertSame('DE-BY', $adminService->getCalendarContext()->subdivisionCode(), 'A rejected calendar save must not change the persisted context.');

// Test 8: Der Controller ist der eigenen App-ID zugeordnet.
$appNameProperty = (new ReflectionClass($controller))->getProperty('appName');
$appNameProperty->setAccessible(true);
assertSame('flz_organization', $appNameProperty->getValue($controller), 'The controller must carry the flz_organization app ID.');
