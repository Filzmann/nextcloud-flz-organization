<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\Permission;

use OCP\Permission\IPermissionProvider;
use OCP\Permission\PermissionDefinition;

/**
 * Permission provider for flz_organization app.
 *
 * This app currently provides administrative configuration only.
 * No user-facing permissions or scopes are granted.
 *
 * Admin routes require Nextcloud administrator authentication.
 * All write operations validate input, use CSRF protection,
 * and store only app-owned AppConfig values (organization definition,
 * calendar context, holiday cache).
 *
 * No personal data is stored or processed by this app.
 * No membership lists, user identifiers, or personal preferences are persisted.
 */
final class PermissionProvider implements IPermissionProvider {

    /**
     * @return array<string, PermissionDefinition>
     */
    public function getPermissionDefinitions(): array {
        // This app currently has no user-grantable permissions.
        // All administrative access is controlled by Nextcloud's
        // native admin authentication, not by app-specific scopes.
        //
        // Organization configuration, calendar context, and holiday cache
        // are managed through admin-only routes that require server admin
        // authentication. No additional permission scopes are introduced.
        return [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getPermissionMapping(): array {
        // No role-based permission mapping for this app.
        // Admin access is determined by Nextcloud's native admin check,
        // not by app-specific role assignments.
        return [];
    }
}
