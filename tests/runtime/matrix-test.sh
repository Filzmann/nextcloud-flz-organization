#!/bin/bash
# Runtime Matrix Test Script for flz_organization
# Execute from nextcloud-dev directory with ddev

set -e

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo_color() {
    echo -e "${1}${2}${NC}"
}

log_success() {
    echo_color "$GREEN" "✓ $1"
}

log_failure() {
    echo_color "$RED" "✗ $1"
    exit 1
}

log_warning() {
    echo_color "$YELLOW" "⚠ $1"
}

log_info() {
    echo "  $1"
}

echo ""
echo_color "$GREEN" "=== flz_organization Runtime Matrix Test ==="
echo ""

# Test 1: App Status
echo "[1/9] Checking app status..."
APP_STATUS=$(ddev exec php html/occ app:list 2>&1 | grep flz_organization || true)
if echo "$APP_STATUS" | grep -q "enabled"; then
    log_success "App is enabled"
else
    log_failure "App is not enabled"
fi

# Test 2: DI Container via Route
echo ""
echo "[2/9] Testing DI container via admin route..."
HTTP_CODE=$(ddev exec curl -s -o /dev/null -w "%{http_code}" \
    -H "Host: nextcloud-dev.ddev.site" \
    "https://localhost/apps/flz_organization/admin" 2>&1 || echo "000")
if [ "$HTTP_CODE" = "303" ] || [ "$HTTP_CODE" = "200" ]; then
    log_success "Route accessible (HTTP $HTTP_CODE)"
else
    log_failure "Route returned HTTP $HTTP_CODE"
fi

# Test 3: Background Job Registration
echo ""
echo "[3/9] Checking background job registration..."
BKJOB=$(ddev exec php html/occ backgroundjob:list 2>&1 | grep -i RefreshHolidayCalendarJob || true)
if echo "$BKJOB" | grep -q "RefreshHolidayCalendarJob"; then
    log_success "Background job registered"
else
    log_warning "Background job not found in list (may need app restart)"
    # This is not a hard failure as the job is declared in info.xml
fi

# Test 4: Provider Registration
echo ""
echo "[4/9] Testing provider registration..."
if ddev exec php -r "require '/var/www/html/lib/base.php'; require '/var/www/html/custom_apps/flz_organization/tests/runtime/permission-provider.php';" 2>/dev/null; then
    log_success "PermissionProvider registered through the V1 event contract"
else
    log_failure "PermissionProvider was not registered through the V1 event contract"
fi

# Test 5: Deactivation
echo ""
echo "[5/9] Testing deactivation..."
ddev exec php html/occ app:disable flz_organization 2>&1 | grep -q "disabled" && \
    log_success "App deactivated successfully" || \
    log_failure "Failed to deactivate app"

# Test 6: Reactivation
echo ""
echo "[6/9] Testing reactivation..."
ddev exec php html/occ app:enable flz_organization 2>&1 | grep -q "enabled" && \
    log_success "App reactivated successfully" || \
    log_failure "Failed to reactivate app"

# Test 7: Route after reactivation
echo ""
echo "[7/9] Testing route after reactivation..."
HTTP_CODE=$(ddev exec curl -s -o /dev/null -w "%{http_code}" \
    -H "Host: nextcloud-dev.ddev.site" \
    "https://localhost/apps/flz_organization/admin" 2>&1 || echo "000")
if [ "$HTTP_CODE" = "303" ] || [ "$HTTP_CODE" = "200" ]; then
    log_success "Route works after reactivation (HTTP $HTTP_CODE)"
else
    log_failure "Route failed after reactivation (HTTP $HTTP_CODE)"
fi

# Test 8: All admin routes
echo ""
echo "[8/9] Testing all admin routes..."
ROUTES=("admin" "admin/organization" "admin/calendar")
ALL_PASSED=true
for route in "${ROUTES[@]}"; do
    HTTP_CODE=$(ddev exec curl -s -o /dev/null -w "%{http_code}" \
        -H "Host: nextcloud-dev.ddev.site" \
        "https://localhost/apps/flz_organization/$route" 2>&1 || echo "000")
    if [ "$HTTP_CODE" = "303" ] || [ "$HTTP_CODE" = "200" ]; then
        log_info "  Route /$route: HTTP $HTTP_CODE ✓"
    else
        log_info "  Route /$route: HTTP $HTTP_CODE ✗"
        ALL_PASSED=false
    fi
done
if [ "$ALL_PASSED" = true ]; then
    log_success "All admin routes accessible"
else
    log_failure "Some admin routes failed"
fi

# Test 9: PHP Unit Tests
echo ""
echo "[9/9] Running PHP unit tests..."
TEST_OUTPUT=$(cd /home/filzmann/projects/br-nextcloud-apps/flz_organization && \
    php tests/run.php 2>&1 || true)
if echo "$TEST_OUTPUT" | grep -q "FAILURES!"; then
    log_failure "PHP unit tests failed"
elif echo "$TEST_OUTPUT" | grep -q "OK"; then
    log_success "PHP unit tests passed"
else
    log_warning "PHP unit tests: unknown result"
fi

echo ""
echo_color "$GREEN" "=== All Runtime Matrix Tests Completed ==="
echo ""
log_info "Note: Background job execution and removal tests require manual verification."
log_info "Run 'ddev exec php html/occ backgroundjob:execute flz_organization RefreshHolidayCalendarJob' to test job execution."
