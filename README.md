# Organization Services

`flz_organization` is the standalone Category-B runtime owner for shared
organization configuration, calendar context, holiday data, and cross-app
absence, conflict, and capability events. It has no LocalBase dependency.

The public PHP contract is `OCA\FlzOrganization\PublicApi\V1`, version `1.0`.
Consumers must check that this app is enabled and that `Contract::supports()`
accepts the required version before resolving further public classes.

Fresh installations start with an invalid organization snapshot until the
canonical definition is explicitly persisted. This is intentional deny-by-
default behavior. The snapshot includes the stable identity key
`works_council_member` for consumers that need the canonical works-council
group without receiving member lists. Historical LocalBase AppConfig is not
imported.

## Tests

```bash
php tests/run.php
node tests/run-js.mjs
```

The PHP test entry uses the development-only `PhpTestRunner` helper from a
sibling `localbase` checkout. This does not add a LocalBase runtime dependency.
The same helper instruments the isolated bootstrap contract and the main
suite when both `PHP_COVERAGE_COMMAND` and `PHP_COVERAGE_OUTPUT_DIR` are set.
PHPCOV includes all `lib/` files, including files the tests never execute.

```bash
PHP_COVERAGE_COMMAND="$(pwd)/../localbase/tests/coverage/vendor/bin/phpcov" \
PHP_COVERAGE_OUTPUT_DIR="/tmp/flz-organization-php-coverage" \
XDEBUG_MODE=coverage php tests/run.php
php ../localbase/tests/coverage/merge-clover.php \
  flz_organization /tmp/flz-organization-php-coverage 67.79

../localbase/tests/coverage/node_modules/.bin/c8 \
  --all '--include=js/**/*.js' --check-coverage --lines=100.00 \
  node tests/run-js.mjs
```

Use a fresh PHP report directory for each measurement. The CI workflow installs
the locked development tools and applies the same regression thresholds.
The initial PHP measurement on 15 November 2026 was **49.95%** (476/953
executable lines). After extending the dependency-light suite from eight to ten
test files, the fresh PHP measurement is **67.79%** (646/953). The added
`AdminControllerTest` covers 68/68 lines and
`RefreshHolidayCalendarJobTest` covers 8/8 lines. The initial JavaScript
measurement on 15 November 2026 was **0.00%** (0/51);
the current suite executes all **51/51 lines (100.00%)** in `js/admin.js`, and
CI enforces that measured threshold. The controller and background-job tests
(`tests/Controller/AdminControllerTest.php`,
`tests/BackgroundJob/RefreshHolidayCalendarJobTest.php`) run as plain-PHP
files inside `tests/run.php` via `tests/suite.php`; no PHPUnit dependency is
required. Overall PHP coverage remains below the 85% target. Runtime, browser,
and full application coverage also remain incomplete.

## Dokumentation

- [Lokalisierungsinventar](docs/l10n-inventory.md)
- [Architecture](docs/architecture.md)
- [Public API V1](docs/public-api-v1.md)
- [Manual acceptance](docs/manual-acceptance.md)
- [Open work](ROADMAP.md)
- [Changes](CHANGELOG.md)
- [License](LICENSE)

