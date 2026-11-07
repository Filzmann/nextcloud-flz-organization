<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\Privacy;

use OCP\Privacy\DataSubject;
use OCP\Privacy\IPersonalDataProvider;

/**
 * Personal data provider for flz_organization app.
 *
 * This app does NOT store or process any personal data.
 * 
 * Rationale:
 * - Organization configuration (roles, areas, hierarchy) stores only
 *   semantic group IDs and labels, never membership lists or user identifiers
 * - Calendar context stores country, subdivision, and timezone (not user-specific)
 * - Holiday cache stores public holiday data indexed by region and date
 * - Admin UI allows configuration changes but does not persist personal data
 * - No user-specific AppConfig, UserConfig, files, or AppData is stored
 * 
 * The app explicitly does NOT:
 * - Store user membership in groups
 * - Store user identifiers (UIDs)
 * - Store personal preferences or settings
 * - Process uploaded files with personal content
 * - Generate reports containing personal data
 * - Collect or aggregate personal data from other apps
 * 
 * Therefore, this provider returns empty results for all queries.
 * 
 * If future versions add personal data storage, this provider must be
 * updated to implement the appropriate methods and the AGENTS.md must be
 * updated to reflect the new data processing scope.
 */
final class PersonalDataProvider implements IPersonalDataProvider {

    /**
     * @return string[] List of data types (empty for this app)
     */
    public function getDataTypes(): array {
        // No personal data types are stored by this app
        return [];
    }

    /**
     * @return array<string, array<string, mixed>> Empty as no personal data exists
     */
    public function getDataForSubject(DataSubject $subject): array {
        // No personal data to return for any subject
        return [];
    }

    /**
     * @return array<string, array<string, mixed>> Empty as no personal data exists
     */
    public function getDataForAllSubjects(): array {
        // No personal data exists for any subject
        return [];
    }

    /**
     * Export all personal data (empty for this app).
     *
     * @return array<string, mixed>
     */
    public function exportAllSubjects(): array {
        // No personal data to export
        return [];
    }

    /**
     * Delete all personal data for a subject (no-op for this app).
     */
    public function deleteDataForSubject(DataSubject $subject): void {
        // No personal data to delete
    }

    /**
     * Delete all personal data for all subjects (no-op for this app).
     */
    public function deleteAllSubjects(): void {
        // No personal data to delete
    }
}
