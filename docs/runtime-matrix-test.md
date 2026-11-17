# Runtime Matrix Test - flz_organization

This document describes the manual and automated tests for the complete application lifecycle.

## Test Matrix

The following matrix covers all required runtime scenarios per ROADMAP:

| Scenario | Test Type | Command | Expected Result | Status |
|----------|-----------|---------|-----------------|--------|
| **DI Container** | Runtime | `occ app:enable flz_organization` | App enables without DI errors | ✅ |
| **DI Container** | Runtime | Access `/apps/flz_organization/admin` | Route resolves, returns login redirect | ✅ |
| **Background Job** | Runtime | `occ backgroundjob:list` | RefreshHolidayCalendarJob appears | ⏳ |
| **Background Job** | Runtime | `occ backgroundjob:execute flz_organization RefreshHolidayCalendarJob` | Job runs without errors | ⏳ |
| **Provider Registration** | Local contract | `php tests/run.php` | PermissionProvider is registered lazily through the V1 event contract | ✅ |
| **Provider Registration** | Runtime | Dispatch the V1 registry event with `flz_permission_matrix` active | PermissionProvider registered exactly once | ⏳ |
| **Provider-Failure** | Runtime | Disable dependency | App remains stable, provider unavailable | ⏳ |
| **Deactivation** | Runtime | `occ app:disable flz_organization` | App disables, no errors | ✅ |
| **Removal** | Runtime | `occ app:remove flz_organization` | App removed, no errors | ⏳ |
| **Reinstall** | Runtime | Reinstall from app store or manual copy | Fresh install works, no migration needed | ✅ |

## Test Commands

### 1. Verify App Status
```bash
occ app:list | grep flz_organization
# Expected: flz_organization <version> enabled
```

### 2. Test Background Job Registration
```bash
occ backgroundjob:list | grep RefreshHolidayCalendarJob
# Expected: flz_organization: RefreshHolidayCalendarJob
```

### 3. Execute Background Job Manually
```bash
occ backgroundjob:execute flz_organization RefreshHolidayCalendarJob
# Expected: Job executes without errors
```

### 4. Test DI Container via Route
```bash
# Without auth (should redirect to login)
curl -I https://<host>/apps/flz_organization/admin
# Expected: HTTP 303 redirect to /login?redirect_url=...

# With admin auth (should return 200)
curl -u admin:password -H "OCS-APIRequest: true" \
  https://<host>/apps/flz_organization/admin
# Expected: HTML response with admin UI
```

### 5. Test Admin Routes
```bash
# Test all admin routes
curl -u admin:password https://<host>/apps/flz_organization/admin
curl -u admin:password https://<host>/apps/flz_organization/admin/organization
curl -u admin:password https://<host>/apps/flz_organization/admin/calendar
# Expected: All return 200 with valid HTML
```

### 6. Test Deactivation
```bash
occ app:disable flz_organization
# Expected: App disabled without errors

# Verify app is disabled
occ app:list | grep flz_organization
# Expected: flz_organization <version> disabled
```

### 7. Test Reactivation
```bash
occ app:enable flz_organization
# Expected: App enabled without errors

# Verify all services work after reactivation
curl -u admin:password https://<host>/apps/flz_organization/admin
# Expected: 200 with valid HTML
```

### 8. Test Removal and Reinstall
```bash
# Remove app
occ app:remove flz_organization
# Expected: App removed without errors

# Reinstall app (copy files back)
rsync -a /path/to/flz_organization/ /var/www/html/custom_apps/flz_organization/
occ app:enable flz_organization
# Expected: Fresh install works, all routes functional
```

### 9. Test Provider Failure Scenarios
```bash
# Test with the optional permission matrix disabled
# Expected: the organization app remains usable; only its permission-inventory
# integration is unavailable.

# Test with corrupted AppConfig (simulate)
# This should be handled gracefully by services
occ app:disable flz_organization
# Manually corrupt config in database
occ config:app:set flz_organization some_invalid_key "invalid_value"
occ app:enable flz_organization
# Expected: App loads, may show warnings but doesn't crash
```

## Automated Test Suite

Run the complete PHP test suite:
```bash
cd /path/to/flz_organization
php tests/run.php
```

Expected: All tests pass with at least 85% line coverage for new code.

## DDEV-Specific Tests

In the workspace DDEV environment:

```bash
# From nextcloud-dev directory
cd /home/filzmann/projects/br-nextcloud-apps/nextcloud-dev

# Run all matrix tests
ddev exec bash -c '
  echo "=== App Status ===" && \
  php html/occ app:list | grep flz_organization && \
  echo "" && \
  echo "=== Background Jobs ===" && \
  php html/occ backgroundjob:list | grep -i holiday && \
  echo "" && \
  echo "=== DI Test ===" && \
  curl -s -o /dev/null -w "%{http_code}" -H "Host: nextcloud-dev.ddev.site" \
    "https://localhost/apps/flz_organization/admin" && echo "" && \
  echo "" && \
  echo "=== All Tests Passed ==="
'
```

## Test Results

- [ ] DI Container test
- [ ] Background Job registration test
- [ ] Background Job execution test
- [ ] Provider registration test
- [ ] Provider failure test
- [ ] Deactivation test
- [ ] Removal test
- [ ] Reinstall test
- [ ] Route accessibility test (admin)
- [ ] Route accessibility test (non-admin)

## Notes

1. The app uses Fresh Install/Reinstall as the authorized transition per architecture.
2. No historical data migration is required or supported.
3. All AppConfig values are recreated on fresh install.
4. No personal data is stored, so no privacy provider cleanup is needed on removal.
5. Background job runs daily at the configured interval (24 hours).

## Last Executed

Date: _____________________
Executor: _________________
Results: __________________
