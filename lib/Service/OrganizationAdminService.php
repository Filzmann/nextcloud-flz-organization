<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\Service;

use OCA\FlzOrganization\Calendar\CalendarContext;
use OCA\FlzOrganization\Calendar\CalendarContextSettingsService;
use OCA\FlzOrganization\Organization\AuthorizationSettingsService;
use OCA\FlzOrganization\Organization\OrganizationDefinition;
use OCA\FlzOrganization\Organization\OrganizationSettingsService;

/**
 * Bereitstellt Admin-spezifische Lese- und Validierungsmethoden für die
 * Organisationskonfiguration.
 * 
 * Dieser Service ist als Vorarbeit für die Admin-Oberfläche gedacht und
 * bietet eine zentrale Schnittstelle für die Verwaltung der Organisations-
 * einstellungen, Kalenderkontexte und Autorisierungsregeln.
 */
final class OrganizationAdminService {
    public function __construct(
        private OrganizationSettingsService $organizationSettings,
        private CalendarContextSettingsService $calendarSettings,
        private AuthorizationSettingsService $authorizationSettings,
    ) {}

    /**
     * Gibt die aktuelle Organisationsdefinition zurück.
     */
    public function getOrganizationDefinition(): OrganizationDefinition {
        return $this->organizationSettings->definition();
    }

    /**
     * Gibt den aktuellen Kalenderkontext zurück.
     */
    public function getCalendarContext(): CalendarContext {
        return $this->calendarSettings->context();
    }

    /**
     * Gibt die aktuellen Autorisierungseinstellungen zurück.
     */
    public function getAuthorizationSettings(): array {
        return [
            'valid' => $this->authorizationSettings->isValid(),
            'calendarPeerEditing' => $this->authorizationSettings->calendarPeerEditing(),
            'vacationPeerEditing' => $this->authorizationSettings->vacationPeerEditing(),
            'vacationPeerApproval' => $this->authorizationSettings->vacationPeerApproval(),
        ];
    }

    /**
     * Validiert eine neue Organisationsdefinition vor dem Speichern.
     * 
     * @param array $data Die zu validierenden Daten
     * @return array{valid:bool, definition:OrganizationDefinition, errors:array}
     */
    public function validateOrganizationDefinition(array $data): array {
        $errors = [];
        
        try {
            $definition = OrganizationDefinition::get($data);
            return ['valid' => true, 'definition' => $definition, 'errors' => []];
        } catch (\InvalidArgumentException $e) {
            $errors[] = $e->getMessage();
            return ['valid' => false, 'definition' => OrganizationDefinition::defaults(), 'errors' => $errors];
        } catch (\Throwable $e) {
            $errors[] = 'Ungültige Organisationsdefinition: ' . $e->getMessage();
            return ['valid' => false, 'definition' => OrganizationDefinition::defaults(), 'errors' => $errors];
        }
    }

    /**
     * Speichert eine neue Organisationsdefinition.
     * 
     * @param array $data Die zu speichernden Daten
     * @return array{success:bool, definition:OrganizationDefinition|null, errors:array}
     */
    public function saveOrganizationDefinition(array $data): array {
        $validation = $this->validateOrganizationDefinition($data);
        
        if (!$validation['valid']) {
            return ['success' => false, 'definition' => null, 'errors' => $validation['errors']];
        }
        
        try {
            $definition = $this->organizationSettings->save($data);
            return ['success' => true, 'definition' => $definition, 'errors' => []];
        } catch (\Throwable $e) {
            return ['success' => false, 'definition' => null, 'errors' => ['Speichern fehlgeschlagen: ' . $e->getMessage()]];
        }
    }

    /**
     * Validiert einen Kalenderkontext vor dem Speichern.
     * 
     * @param array $data Die zu validierenden Daten
     * @return array{valid:bool, context:CalendarContext|null, errors:array}
     */
    public function validateCalendarContext(array $data): array {
        $errors = [];
        
        try {
            $context = CalendarContext::get($data);
            return ['valid' => true, 'context' => $context, 'errors' => []];
        } catch (\InvalidArgumentException $e) {
            $errors[] = $e->getMessage();
            return ['valid' => false, 'context' => null, 'errors' => $errors];
        } catch (\Throwable $e) {
            $errors[] = 'Ungültiger Kalenderkontext: ' . $e->getMessage();
            return ['valid' => false, 'context' => null, 'errors' => $errors];
        }
    }

    /**
     * Speichert einen neuen Kalenderkontext.
     * 
     * @param array $data Die zu speichernden Daten
     * @return array{success:bool, context:CalendarContext|null, errors:array}
     */
    public function saveCalendarContext(array $data): array {
        $validation = $this->validateCalendarContext($data);
        
        if (!$validation['valid']) {
            return ['success' => false, 'context' => null, 'errors' => $validation['errors']];
        }
        
        try {
            $context = $this->calendarSettings->save($data);
            return ['success' => true, 'context' => $context, 'errors' => []];
        } catch (\Throwable $e) {
            return ['success' => false, 'context' => null, 'errors' => ['Speichern fehlgeschlagen: ' . $e->getMessage()]];
        }
    }

    /**
     * Gibt einen Überblick über den aktuellen Konfigurationsstatus zurück.
     * 
     * @return array{organization:array, calendar:array, authorization:array, valid:bool}
     */
    public function getConfigurationStatus(): array {
        $orgState = $this->organizationSettings->state();
        $calendarContext = $this->calendarSettings->context();
        $authValid = $this->authorizationSettings->isValid();
        
        return [
            'organization' => [
                'valid' => $orgState['valid'],
                'persisted' => $orgState['persisted'],
                'definition' => $orgState['definition']->toArray(),
            ],
            'calendar' => [
                'countryCode' => $calendarContext->countryCode(),
                'subdivisionCode' => $calendarContext->subdivisionCode(),
                'timezone' => $calendarContext->toArray()['timezone'],
            ],
            'authorization' => [
                'valid' => $authValid,
                'calendarPeerEditing' => $this->authorizationSettings->calendarPeerEditing(),
                'vacationPeerEditing' => $this->authorizationSettings->vacationPeerEditing(),
                'vacationPeerApproval' => $this->authorizationSettings->vacationPeerApproval(),
            ],
            'valid' => $orgState['valid'] && $authValid,
        ];
    }
}
