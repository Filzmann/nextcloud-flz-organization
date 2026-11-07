# Changelog

## Unreleased

- Created the standalone `flz_organization` Category-B runtime app.
- Extended the public organization snapshot with validated identity groups,
  including the canonical `works_council_member` identity consumed by the
  permission matrix.
- Added public V1 organization snapshot and authorization services, calendar
  context and holiday providers, and absence/conflict/capability events.
- Added fail-closed AppConfig ownership, holiday refresh infrastructure, and
  dependency-light provider contract tests.
- Added CSRF-protected administration surface for organization definition,
  calendar context, and authorization settings configuration.
- Added background job for daily holiday calendar cache refresh.
- Added PermissionProvider and PersonalDataProvider for privacy compliance.
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

