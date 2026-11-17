<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\Controller;

use OCA\FlzOrganization\Service\OrganizationAdminService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;

/**
 * Controller für die Admin-Oberfläche der flz_organization App.
 * 
 * Bietet CSRF-geschützte Endpunkte für die Konfiguration von:
 * - Organisationsdefinitionen (Rollen, Bereiche, Identitätsgruppen)
 * - Kalenderkontext (Land, Region, Zeitzone)
 * - Lesestatus der Autorisierungseinstellungen
 */
class AdminController extends Controller
{
    public function __construct(
        IRequest $request,
        private OrganizationAdminService $adminService,
    )
    {
        parent::__construct('flz_organization', $request);
    }

    /**
     * Zeigt die Admin-Übersichtsseite an.
     */
    public function index(): TemplateResponse
    {
        $status = $this->adminService->getConfigurationStatus();
        
        return new TemplateResponse(
            'flz_organization',
            'admin/index',
            [
                'status' => $status,
            ],
            TemplateResponse::RENDER_AS_GUEST
        );
    }

    /**
     * Zeigt das Formular für die Organisationsdefinition an.
     */
    public function organizationForm(): TemplateResponse
    {
        $definition = $this->adminService->getOrganizationDefinition();
        
        return new TemplateResponse(
            'flz_organization',
            'admin/organization-form',
            [
                'definition' => $definition->toArray(),
            ],
            TemplateResponse::RENDER_AS_GUEST
        );
    }

    /**
     * Speichert die Organisationsdefinition.
     */
    public function saveOrganization(array $data): TemplateResponse
    {
        $result = $this->adminService->saveOrganizationDefinition($data);
        
        if (!$result['success'])
        {
            return new TemplateResponse(
                'flz_organization',
                'admin/organization-form',
                [
                    'definition' => $data,
                    'errors' => $result['errors'],
                ],
                TemplateResponse::RENDER_AS_GUEST
            );
        }
        
        return new TemplateResponse(
            'flz_organization',
            'admin/organization-form',
            [
                'definition' => $result['definition']->toArray(),
                'saved' => true,
            ],
            TemplateResponse::RENDER_AS_GUEST
        );
    }

    /**
     * Zeigt das Formular für den Kalenderkontext an.
     */
    public function calendarForm(): TemplateResponse
    {
        $context = $this->adminService->getCalendarContext();
        
        return new TemplateResponse(
            'flz_organization',
            'admin/calendar-form',
            [
                'context' => $context->toArray(),
            ],
            TemplateResponse::RENDER_AS_GUEST
        );
    }

    /**
     * Speichert den Kalenderkontext.
     */
    public function saveCalendar(array $data): TemplateResponse
    {
        $result = $this->adminService->saveCalendarContext($data);
        
        if (!$result['success'])
        {
            return new TemplateResponse(
                'flz_organization',
                'admin/calendar-form',
                [
                    'context' => $data,
                    'errors' => $result['errors'],
                ],
                TemplateResponse::RENDER_AS_GUEST
            );
        }
        
        return new TemplateResponse(
            'flz_organization',
            'admin/calendar-form',
            [
                'context' => $result['context']->toArray(),
                'saved' => true,
            ],
            TemplateResponse::RENDER_AS_GUEST
        );
    }
}
