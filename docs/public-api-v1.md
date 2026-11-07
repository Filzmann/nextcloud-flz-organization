# Public API V1

Provider app ID: `flz_organization`  
Namespace: `OCA\FlzOrganization\PublicApi\V1`  
Contract version: `1.0`

Consumers first check native app activation, then `class_exists(Contract::class)`,
then `Contract::supports('1.0')`. Only after those checks may they resolve
other public services. Missing, disabled, or incompatible providers must be
reported or handled as unavailable without a fatal error or LocalBase fallback.

## Organization and authorization

`OrganizationSnapshotService::snapshot()` returns semantic role and area
group mappings, the complete validated definition needed for ordering and
presentation, plus named organization identity groups without member lists.
The stable identity key `works_council_member` resolves the canonical
works-council membership group used by authorized consumers. Invalid
configuration returns `valid=false` with all mappings empty; missing,
duplicate, or malformed identity groups invalidate the whole snapshot.

`OrganizationAuthorizationService::canManage()` evaluates hierarchy, shared
areas, self-service, and configured peer groups for the stable scopes
`calendar.edit`, `vacation.edit`, and `vacation.approve`. It never interprets
native Nextcloud administration as a fachlich authorized bypass. Unknown
scopes are invalid; invalid provider state denies. `peerGroups()` exposes only
the enabled group IDs for one of these stable scopes.

## Calendar

`CalendarContextProvider` exposes country, ISO-3166-2 subdivision, and the
organization IANA timezone. `HolidayCalendarProvider` exposes public holiday
dates, labels and inclusive periods plus explicit cache status. `stale` remains usable with a visible
freshness limitation; `unavailable` is not usable.

## Events

The public event identities are `AbsenceEmployeeDiscoveryEvent`,
`AbsenceQueryEvent`, `ScheduleConflictQueryEvent`, and
`IntegrationCapabilityQueryEvent`, with their immutable value objects.
Intervals are half-open. Providers may contribute only requested UIDs,
overlapping intervals, non-self conflict sources, and requested capability
keys. No listener is a valid standalone result. Capability availability never
grants permission.

V1 changes are additive. A breaking semantic or type change requires a new
major public namespace and consumer migration.

