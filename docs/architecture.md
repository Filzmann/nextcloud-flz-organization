# Architecture

## Runtime boundary

This app is an independent Nextcloud Category-B runtime app under ADR 0001.
Its recorded shared-code choice is **without a shared runtime dependency**:
it depends on Nextcloud public APIs but neither on LocalBase nor on another
workspace app. Consumer apps use only `OCA\FlzOrganization\PublicApi\V1`.

The app owns these canonical AppConfig values under `flz_organization`:

- `organization_definition` for semantic roles, areas, identity groups,
  hierarchy, and teams;
- `authorization_settings` for calendar and vacation peer scopes;
- `calendar_context` for country, subdivision, and organization timezone;
- region- and year-bound holiday cache entries.

Fresh install/reinstall is the authorized development transition. No
LocalBase key, table, file, or private service is read and no historical
configuration is migrated. Missing or malformed organization configuration
produces an invalid, empty snapshot and denies authorization. Deactivation
retains native app-owned state but removes the runtime provider; removal and
reinstall intentionally recreate the development configuration. Consumer
apps must treat a missing, disabled, or incompatible provider as a controlled
state and never resolve private classes or use LocalBase as fallback.

## Public V1

The public contract is documented in [public-api-v1.md](public-api-v1.md).
`OrganizationSettingsService`, authorization settings, holiday cache, and the
OpenHolidays adapter are internal. The public API exposes only read-only
snapshots, authorization decisions, and single-identity event objects.

Native Nextcloud administration does not grant an authorization bypass.
App-local temporary admin access remains the responsibility of each consumer
under ADR 0004 and is deliberately not part of this V1 service.

## State and failures

Organization writes validate semantic keys, unique group IDs, references,
and acyclic hierarchy before persisting. The canonical works-council
membership is the identity group `works_council_member`; it exposes only the
group ID and label, never a member list. Authorization evaluates the current
valid definition and settings on every call. Unknown scopes throw a stable
validation error; malformed persistence denies without leaking raw values.

Holiday data uses a bounded HTTPS provider adapter, validates normalized
periods, stores a small AppConfig cache, and distinguishes `current`, `fresh`,
`stale`, and `unavailable`. `unavailable` is never represented as a usable
empty holiday list. The daily job refreshes the current and two following
years. Provider errors retain a dated stale cache where available and are
logged without personal content.

## Privacy and permission providers

No `PersonalDataProvider` is applicable to the initial scope. The app stores
organization group IDs, labels, calendar configuration, and public holiday
cache data, but no user membership, UID, file, report, or other app-owned
personal value. Event objects can transiently carry a UID only after a
consumer or provider creates them; this app registers no listener that
collects or persists such values. The data-owning listener app remains
responsible for its own processing catalog and privacy provider.

No `PermissionProvider` is applicable to the initial scope because the app
has no user-facing or administrative endpoint and grants no app-local actor
permission. Its read-only authorization service supplies inputs to consumer
apps; those consumers remain responsible for server-side access decisions
and their own permission providers. Both decisions must be reevaluated when
the app gains an admin UI, user endpoints, membership data, or personal
configuration.

## Release and rollback

There is no LocalBase runtime or packaging dependency. A rollback during the
development phase removes this app and restores the previous consumer version;
no production or historical data preservation contract exists. Public App
Store release is not authorized and requires a separate review of the
external runtime dependency model and licenses, including OpenHolidays/ODbL.

