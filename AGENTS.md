# AGENTS.md – Organization Services

## Repository and scope

This is the separately versioned Nextcloud app repository `flz_organization`.
Its open work is in `ROADMAP.md`; current architecture is in
`docs/architecture.md`. Work starts from this root and uses the local
`work-in-nextcloud-app` skill. Observable behavior changes also use the local
`test-driven-change` skill.

The app is the standalone Category-B owner for organization configuration,
calendar context and holiday cache, plus the public cross-app event identities.
It has no LocalBase runtime dependency. App code, tests, documentation and
configuration remain in this repository.

## Binding architecture and security

- Public runtime API is restricted to `OCA\\FlzOrganization\\PublicApi\\V1`
  and contract version `1.0`.
- Consumers must check native app activation, class availability and supported
  version before resolving public services. Missing or incompatible providers
  remain controlled unavailable states without fatal error or fallback.
- No consumer may access this app's private AppConfig keys, internal classes,
  files, routes or assets.
- Organization configuration is the single source for semantic role, area and
  hierarchy mappings. Missing, malformed, duplicate or cyclic state fails
  closed and grants no authorization.
- Native Nextcloud administration never grants implicit fachlich access.
  Consumer apps own their server-side authorization and any temporary
  app-admin grant under ADR 0004.
- Calendar context and holiday cache are app-owned. `unavailable` is never
  treated as an authoritative empty holiday list.
- Absence, conflict and capability events are read-only contracts. Capability
  availability does not grant permissions.
- Controllers, if introduced, remain thin; writes require validation, typed
  input, CSRF protection, and explicit authorization.
- No SQL, reflection, filesystem or private-config fallback to LocalBase or
  consumer apps is permitted.
- The authorized transition is Fresh Install/Reinstall. Historical LocalBase
  configuration is not migrated or supported.

## Data and provider decisions

The initial scope stores no app-owned personal identifiers or membership
lists. Event types may transiently carry UIDs only when instantiated by their
data-owning consumers/providers; this app registers no collector. Therefore a
PersonalDataProvider and processing catalog are currently not applicable.

The app exposes a native Nextcloud-admin-only configuration surface. Its read
and write endpoints retain the framework's default administrator check; writes
also retain the default CSRF check. It owns no app-local actor permission, and
its empty PermissionProvider documents that no user-grantable app scope or
role mapping exists. These decisions must be reevaluated with every new user
endpoint, persisted personal value, membership source or permission scope.
Unknown coverage is never described as complete.

## Tests and completion

Run from this repository:

    php tests/run.php
    node tests/run-js.mjs

Public contract changes require provider tests here and consumer tests in every
separately authorized affected repository. Security boundaries include a
meaningful denied or malformed case with no forbidden side effect. Aim for at
least 85 percent line coverage of new executable PHP; JavaScript coverage is
reported separately if JavaScript is introduced.

DDEV, Docker, Nextcloud, `occ`, installation and removal commands require a
concrete request or explicit approval. No commit, push, release or deployment
without Simon's explicit authorization.

## Dokumentenverantwortung

- `README.md` ist für den aktuellen nutzbaren Umfang, Installation/Betrieb,
  Tests und den Dokumentationsindex verantwortlich.
- `ROADMAP.md` enthält ausschließlich offene, zurückgestellte oder
  freigabepflichtige Arbeit.
- `CHANGELOG.md` dokumentiert abgeschlossene oder für einen Release
  vorbereitete Änderungen.
- `docs/architecture.md`: current domain, data and integration contracts.
- `docs/public-api-v1.md`: public V1 consumer contract.
- `docs/manual-acceptance.md`: repeatable unfilled acceptance instruction.
- `AGENTS.md`: binding work, security, architecture and verification rules.

<!-- APP-AGENTS-GOVERNANCE:START -->
## Parent-Governance-Vertrag: 2

- Die für dieses Subrepository anwendbaren Regeln des Parent-Workspaces sind
  verbindlich. Dazu gehören insbesondere app-übergreifende ADRs und
  öffentliche Verträge, Repositorygrenzen sowie Workspace-, Delivery- und
  Release-Gates.
- Diese lokale `AGENTS.md` und die lokalen Skills bleiben die vollständige,
  ohne Parent-Checkout arbeitsfähige Repository-Steuerung. Die anwendbaren
  Parent-Regeln werden dafür hier oder in den lokalen Skills mitgeführt.
- Repository-lokale Regeln dürfen Parent-Verträge konkretisieren und verschärfen,
  aber nicht abschwächen oder umgehen.
- Bei einem Widerspruch gilt bis zur Klärung die strengere Regel. Die Arbeit
  stoppt, bis die kanonische Quelle bestimmt, die Regelprojektionen
  synchronisiert und eine erforderliche Entscheidung dokumentiert ist.
- Ist der Parent-Workspace nicht verfügbar, bleibt die lokale Steuerung
  wirksam. Vor Cross-App-, Release- oder Delivery-Arbeit muss ein vermuteter
  neuerer Parent-Stand oder eine Regelungslücke zuerst gegen den Parent
  geprüft werden.

### Entwicklungsphase und Kompatibilitätsbedarf

Entscheidung vom 5. September 2026: Das Gesamtprojekt befindet sich vollständig
in der Entwicklung. Es gibt kein PROD, keinen produktiven Datenbestand und
keinen bereits betriebenen Bestand mit zu erhaltendem Upgradepfad. STAGING
ist eine wegwerfbare Entwicklungs- und Integrationsumgebung und darf im
konkret beauftragten Reinstall vollständig neu aufgebaut werden. Wenige
externe Testnutzer ändern diese Einordnung nicht.

Vor einer Datenmigration, Legacy-Unterstützung, Compatibility Layer,
Deprecated API, Dual-Read/Dual-Write, einem Altschema-Fallback, Übergangsformat
oder der Unterstützung historischer Entwicklungsstände wird geprüft:

1. Wurde der betroffene Zustand jemals produktiv eingesetzt?
2. Benötigen reale Daten oder Nutzer seine Erhaltung?
3. Gibt es einen anderen konkreten technischen Erhaltungsgrund, insbesondere
   einen geltenden Plattform- oder externen API-Vertrag?

Sind alle relevanten Antworten nein, ist die saubere Breaking-Change-/
Reinstall-Lösung der Standard. Frühere rein interne Entwicklungsstände
begründen weder Abwärtskompatibilität noch eine Deprecationfrist.
Entwicklungsschemata dürfen durch ein kanonisches Installationsschema ersetzt,
alte interne APIs und Konfigurationsformate samt ausschließlich dafür
benötigten Adaptern und Tests entfernt werden. Architekturqualität und der
saubere Zielzustand haben Vorrang. Nextclouds nötige Installationsmigrationen
bleiben erhalten; ein Verzeichnisname `Migration` beweist keine Altlast.

Breaking Changes werden im selben Änderungskontext vollständig durchgezogen:
betroffene Provider, Consumer, standardisierte APIs, Vertragsversionen,
Metadaten, Tests und Dokumentation müssen zusammenpassen. Unterstützte
Nextcloud-/openDesk-Plattformverträge, externe Standards, Autorisierung und
Datenschutz gelten unverändert. Fehlende oder inkompatible optionale Provider
bleiben kontrolliert sichtbar. Ein Reinstall erlaubt keine privaten
Fremdtabellenzugriffe oder parallel erfundenen Plattformmechanismen.

Vor destruktiver Arbeit werden die tatsächlich benötigten externen
Testidentitäten, Gruppen, Rollen und nicht reproduzierbaren Testdaten gezielt
gesichert oder über bestehende native Setup-Strukturen reproduzierbar gemacht.
Echte Personen- und Zugangsdaten bleiben außerhalb von Git. Diese begrenzte
Sicherung begründet keine allgemeine Legacy-Unterstützung. Ein Reinstall
bleibt ein normaler unterstützter Entwicklungsweg; der vorhandene
Compatibility-Workflow besitzt den Fresh-Install-Nachweis, dessen aktueller
Belegstatus in `docs/workspace.md` beschrieben ist.

Diese Phase endet ausschließlich durch einen ausdrücklich dokumentierten,
von Simon freigegebenen **Production-Readiness-/Production-Freeze-Entscheid**.
Ein Release Candidate, eine Versionsnummer, ein Staging-Deployment oder ein
externer Testzugang lösen den Wechsel nicht aus. Der Entscheid wird in dieser
kanonischen Lifecycle-Quelle mit Datum, Geltungsbereich und betroffenem
Versions-/Datenstand festgehalten und in die lokale Steuerung projiziert.
Dann werden Upgradepfade, Datenbankmigrationen, Persistenz, Backup/Restore,
Rollback, Release-/API-Kompatibilitätszusagen, Deployment-/Freigabeprozess und
PROD→STAGING/COPY-Strategie neu bewertet. Eine vollständige PROD-Governance
wird jetzt nicht vorweggenommen.

Diese Regel entscheidet den Kompatibilitätsbedarf, erweitert aber keinen
Repository-Schreibauftrag und ersetzt keine Freigabe für eine konkrete
destruktive Aktion. Lokale Regelprojektionen folgen dem bestehenden
`docs/parent-governance-contract.md`; ein unsynchronisierter Einzel-Checkout
darf keinen abweichenden Phasenstand stillschweigend annehmen.

### Prüfaufwand

- Vor einem neuen Test, Scan, Linter, Architektur- oder Systemcheck wird
  geprüft, welcher bestehende Check dieselbe Eigenschaft bereits nachweist.
  Diesen erweitern oder sein nachweislich passendes Ergebnis wiederverwenden;
  ein zusätzlicher Check braucht eine benannte zusätzliche Fehlerklasse oder
  Vertrauensgrenze. Gleicher Input, gleiche Prüfung, gleiche Fehlerklasse und
  gleiche Phase begründen keinen zweiten Lauf. Gestaffelte Unit-, Contract-
  und Runtime-Nachweise bleiben erhalten. Die dokumentierten lokalen
  Prüfeinstiege bestimmen Umfang und Ergebnisgültigkeit.
<!-- APP-AGENTS-GOVERNANCE:END -->

