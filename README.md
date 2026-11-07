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

## Documentation

- [Architecture](docs/architecture.md)
- [Public API V1](docs/public-api-v1.md)
- [Manual acceptance](docs/manual-acceptance.md)
- [Open work](ROADMAP.md)
- [Changes](CHANGELOG.md)
- [License](LICENSE)

