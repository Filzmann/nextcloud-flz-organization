<?php

declare(strict_types=1);

use OCA\FlzOrganization\AppInfo\Application;
use OCA\FlzOrganization\Organization\AuthorizationSettingsService;
use OCA\FlzOrganization\Organization\OrganizationDefinition;
use OCA\FlzOrganization\Organization\OrganizationSettingsService;
use OCA\FlzOrganization\Tests\FakeAppConfig;

// Vertrag: Die validierte Organisationsdefinition muss ihre Zugriffs-, Migrations- und Validierungszweige vollständig.testbar abbilden.

$defaults = OrganizationDefinition::defaults();

// get_all + save()-Verbotszweig
$all = OrganizationDefinition::get_all([$defaults->toArray(), $defaults->toArray()]);
assertSame(2, count($all), 'get_all muss jede Definition einzeln laden.');
assertThrows(static fn() => $defaults->save(), LogicException::class, 'Direktes Speichern einer Definition ist verboten.');

// einfache Daten-Getter
assertSame('ASN-', $defaults->teamGroupPrefix(), 'Team-Gruppenpräfix fehlt.');
assertSame('Assistenzteam', $defaults->teamLabelPrefix(), 'Team-Label-Präfix fehlt.');
assertSame(16, $defaults->teamCodeMaxLength(), 'Teamkürzel-Maximum fehlt.');
assertSame('Geschäftsführung, Leitungen und Stabsstellen', $defaults->staffBlockLabel(), 'Stabsblock-Titel fehlt.');
assertTrue(isset($defaults->roles()['office'], $defaults->areas()['west'], $defaults->identityGroups()['works_council_member']), 'Kernfelder fehlen.');
assertTrue(is_array($defaults->hierarchy()) && is_array($defaults->diagramOrder()) && is_array($defaults->organizationTeams()), 'Hierarchie-, Diagramm- und Teamfelder fehlen.');

// Label- und Gruppen-Getter inkl. Fallbacks
assertSame('Büromitarbeiter*innen', $defaults->roleLabel('office'), 'Rollenlabel falsch.');
assertSame('unbekannt', $defaults->roleLabel('unbekannt'), 'Rollenlabel-Fallback fehlt.');
assertSame('Bereich-West', $defaults->areaGroupId('west'), 'Bereichsgruppen-ID falsch.');
assertSame(null, $defaults->areaGroupId('unbekannt'), 'Bereichsgruppen-Fallback muss null sein.');
assertSame('West', $defaults->areaLabel('west'), 'Bereichslabel falsch.');
assertSame('unbekannt', $defaults->areaLabel('unbekannt'), 'Bereichslabel-Fallback fehlt.');
assertSame('Büromitarbeiter*innen', $defaults->roleLabelForGroup('Buero'), 'Rollenlabel zur Gruppe falsch.');
assertSame('Fremd', $defaults->roleLabelForGroup('Fremd'), 'Rollenlabel-Fallback zur Gruppe fehlt.');
assertSame('West', $defaults->areaLabelForGroup('Bereich-West'), 'Bereichslabel zur Gruppe falsch.');
assertSame('Fremd', $defaults->areaLabelForGroup('Fremd'), 'Bereichslabel-Fallback zur Gruppe fehlt.');
assertSame('BO', $defaults->roleShortLabelForGroup('Buero'), 'Rollenkürzel zur Gruppe falsch.');
assertSame('NO', $defaults->areaShortLabelForGroup('Bereich-Nordost'), 'Bereichskürzel zur Gruppe falsch.');
assertTrue($defaults->roleIsAreaScopedByGroup('Buero'), 'Büro ist bereichsscoped.');
assertTrue(!$defaults->roleIsAreaScopedByGroup('Sekretariat'), 'Sekretariat ist nicht bereichsscoped.');
assertTrue(!$defaults->roleIsAreaScopedByGroup('Fremd'), 'Unbekannte Gruppe ist nicht bereichsscoped.');
assertTrue($defaults->roleManagementIsAreaScoped('bl'), 'Büroleitung verwaltet bereichsscoped.');
assertTrue(!$defaults->roleManagementIsAreaScoped('office'), 'Büro verwaltet nicht bereichsscoped.');
assertSame('office', $defaults->roleKeyForGroup('Buero'), 'Rollenschlüssel zur Gruppe falsch.');
assertSame(null, $defaults->roleKeyForGroup('Fremd'), 'Unbekannte Gruppe muss null liefern.');

// Teamkürzel-Normalisierung
assertSame('Team01', $defaults->normalizeTeamCode('  Team01  '), 'Teamkürzel wird nicht getrimmt.');
assertThrows(static fn() => $defaults->normalizeTeamCode(''), InvalidArgumentException::class, 'Leeres Teamkürzel muss abgelehnt werden.');
assertThrows(static fn() => $defaults->normalizeTeamCode(str_repeat('a', 17)), InvalidArgumentException::class, 'Übergroßes Teamkürzel muss abgelehnt werden.');
assertThrows(static fn() => $defaults->normalizeTeamCode('Team-01'), InvalidArgumentException::class, 'Sonderzeichen im Teamkürzel müssen abgelehnt werden.');

// roleGroupIds mit und ohne Filter
assertTrue(in_array('Buero', $defaults->roleGroupIds(), true), 'roleGroupIds ohne Filter muss alle Gruppen liefern.');
$staffOnly = $defaults->roleGroupIds(static fn(array $role): bool => $role['staffBlock']);
assertTrue(in_array('Sekretariat', $staffOnly, true) && !in_array('Buero', $staffOnly, true), 'roleGroupIds mit Filter falsch.');

// Hierarchie-Auflösung inkl. Zyklusschutz
assertTrue($defaults->managesRole('gf_as', 'pfk'), 'gf_as muss pfk verwalten.');
assertTrue(!$defaults->managesRole('pfk', 'gf_as'), 'pfk darf gf_as nicht verwalten.');
assertTrue(!$defaults->managesRole('office', 'office'), 'Zyklus darf nicht als Verwaltung gelten.');

// Migration v1 -> v5 und ungültige Version
$legacy = $defaults->toArray();
$legacy['version'] = 1;
unset($legacy['identityGroups'], $legacy['diagramOrder']);
$migrated = OrganizationDefinition::get($legacy);
assertSame(5, $migrated->toArray()['version'], 'Migration muss auf Version 5 enden.');
assertTrue(isset($migrated->identityGroups()['works_council_member']), 'Migration muss die BR-Identitätsgruppe ergänzen.');
assertTrue(isset($migrated->roles()['payroll']), 'Migration muss die Lohn-Rolle ergänzen.');

$badVersion = $defaults->toArray();
$badVersion['version'] = 99;
assertThrows(static fn() => OrganizationDefinition::get($badVersion), InvalidArgumentException::class, 'Unbekannte Organisationsversion muss abgelehnt werden.');

// Validierungsfehler der Diagrammordnung
$badNode = $defaults->toArray();
$badNode['diagramOrder'] = ['unbekannt'];
assertThrows(static fn() => OrganizationDefinition::get($badNode), InvalidArgumentException::class, 'Unbekannter Diagrammknoten muss abgelehnt werden.');
$dupNode = $defaults->toArray();
$dupNode['diagramOrder'] = ['gf_as', 'gf_as'];
assertThrows(static fn() => OrganizationDefinition::get($dupNode), InvalidArgumentException::class, 'Doppelter Diagrammknoten muss abgelehnt werden.');
$badOrder = $defaults->toArray();
$badOrder['diagramOrder'] = 'kein-array';
assertThrows(static fn() => OrganizationDefinition::get($badOrder), InvalidArgumentException::class, 'Diagrammordnung ohne Array muss abgelehnt werden.');

// AuthorizationSettingsService: Defaults, Persistenz und Schema-Fehlerzweige
$config = new FakeAppConfig();
$settings = new OrganizationSettingsService($config);
$settings->save(OrganizationDefinition::defaults()->toArray());
$authorizations = new AuthorizationSettingsService($config, $settings);

$calendarDefaults = $authorizations->calendarPeerEditing();
assertTrue(($calendarDefaults['Buero'] ?? false) === true, 'Kalender-Default muss Büro erlauben.');
assertTrue(($calendarDefaults['Sekretariat'] ?? true) === false, 'Kalender-Default darf Sekretariat nicht erlauben.');
assertTrue($authorizations->isValid(), 'Leere Autorisierungseinstellungen sind valide.');

$saved = $authorizations->saveVacationPeerApproval(['Sekretariat' => true, 'Buero' => 'yes']);
assertTrue(($saved['Sekretariat'] ?? false) === true, 'Speichern muss boolesche Werte übernehmen.');
assertTrue(($saved['Buero'] ?? false) === true, 'FILTER_VALIDATE_BOOL muss "yes" als true werten.');
$authorizations->saveCalendarPeerEditing(['Buero' => false]);
$authorizations->saveVacationPeerEditing(['Buero' => true]);
assertTrue(($authorizations->calendarPeerEditing()['Buero'] ?? true) === false, 'Gespeicherter Kalender-Wert muss gelesen werden.');
assertSame($saved, $authorizations->vacationPeerApproval(), 'Getter muss gespeicherte Werte liefern.');

$config->inject(Application::APP_ID, AuthorizationSettingsService::CONFIG_KEY, '{broken');
assertTrue(!$authorizations->isValid(), 'Kaputte Autorisierungseinstellungen müssen invalide sein.');
foreach ($authorizations->calendarPeerEditing() as $enabled) {
    assertTrue($enabled === false, 'Kaputte Einstellungen müssen alle Scopes auf false liefern.');
}
$reset = $authorizations->saveCalendarPeerEditing(['Buero' => true]);
assertTrue($authorizations->isValid(), 'Speichern muss kaputte Einstellungen zurücksetzen.');
assertTrue(($reset['Buero'] ?? false) === true, 'Zurückgesetzte Einstellungen müssen den neuen Wert liefern.');

$config->inject(Application::APP_ID, AuthorizationSettingsService::CONFIG_KEY, json_encode(['version' => 2], JSON_THROW_ON_ERROR));
assertTrue(!$authorizations->isValid(), 'Falsche Schema-Version muss invalide sein.');
$config->inject(Application::APP_ID, AuthorizationSettingsService::CONFIG_KEY, json_encode(['calendarPeerEditing' => 'kein-array'], JSON_THROW_ON_ERROR));
assertTrue(!$authorizations->isValid(), 'Nicht-Array-Scope muss invalide sein.');
$config->inject(Application::APP_ID, AuthorizationSettingsService::CONFIG_KEY, json_encode(['vacationPeerApproval' => ['Buero' => 'kein-bool']], JSON_THROW_ON_ERROR));
assertTrue(!$authorizations->isValid(), 'Nicht-boolescher Scope-Wert muss invalide sein.');

echo "OrganizationDefinitionContractTest: OK\n";
