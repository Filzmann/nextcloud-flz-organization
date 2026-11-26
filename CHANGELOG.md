# Changelog

## Unreleased

- Provider for the public flz_kapazitaet V1 contract `AssistantContextQueryEvent`: new `Listener/AssistantContextListener` answers with the ASN team in the logged-in user's office (team + assistants from the public flzplaner V1 catalog `AcuteShiftCatalogProvider::teamsForBureaux` for the user's areas via the organization definition), fail-closed without session/valid definition/area/planer. Registered in `Application::register` (fires only when flz_kapazitaet is installed). New `Service/PlanerTeamCatalogGateway` wraps the planer catalog read-only. Test: `tests/Service/AssistantContextListenerTest.php` (provider + fail-closed cases).
- Expanded the dependency-light PHP suite from eight to ten test files with
  controller and background-job coverage (68/68 and 8/8 lines). The fresh PHP
  measurement increased from 49.95% (476/953) to 67.79% (646/953); CI now
  enforces the new threshold. Overall PHP, runtime, browser, and full
  application coverage remain incomplete.
- Added executable administration JavaScript behavior tests; the measured
  `js/admin.js` line coverage is now 100.00% (51/51), enforced in CI. The
  original 0.00% measurement remains documented as the starting point.
- Added reproducible PHP/JavaScript coverage CI and initial measured regression
  thresholds of 49.95% PHP and 0.00% JavaScript, with uncovered files included.
- Instrumented both isolated bootstrap and main PHP tests through the existing
  development-only runner and made the incomplete UI coverage explicit.
- Created the standalone `flz_organization` Category-B runtime app.
- Extended the public organization snapshot with validated identity groups,
  including the canonical `works_council_member` identity consumed by the
  permission matrix.
- Added public V1 organization snapshot and authorization services, calendar
  context and holiday providers, and absence/conflict/capability events.
- Added fail-closed AppConfig ownership, holiday refresh infrastructure, and
  dependency-light provider contract tests.
- Added a CSRF-protected administration surface for organization definition
  and calendar-context configuration, with read-only authorization status.
- Added background job for daily holiday calendar cache refresh.
- Added a lazy V1 PermissionProvider integration for the permission matrix;
  the complete empty inventory documents that the app grants no app-owned
  actor permissions.
- Removed the synthetic PersonalDataProvider because the app owns no personal
  values and the provider is therefore not applicable.
- Replaced unsupported bootstrap registration calls and the invalid service
  alias factory with supported Nextcloud event-listener registration.
- Added comprehensive unit and integration tests for new components.
- Added runtime matrix test documentation for DI, background-job, deactivation,
  removal, and reinstall scenarios.

## 0.1.0-dev.2 - 2026-11-07

- Initial administration surface implementation with CSRF protection
- Version bump to 0.1.0-dev.2 in info.xml
- Added admin navigation entry in Nextcloud admin panel

## 0.1.0-dev.1 - 2026-10-29

- Initial app structure with public API V1 contracts
- Organization snapshot, authorization, calendar, and holiday services
- Public event system for absence, conflict, and capability queries

