<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\Listener;

use OCA\FlzKapazitaet\PublicApi\V1\AssistantContext;
use OCA\FlzKapazitaet\PublicApi\V1\AssistantContextQueryEvent;
use OCA\FlzOrganization\Organization\OrganizationSettingsService;
use OCA\FlzOrganization\Service\PlanerTeamCatalogGateway;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IGroupManager;
use OCP\IUserSession;

/**
 * Provider für den öffentlichen flz_kapazitaet-V1-Vertrag AssistantContextQueryEvent:
 * liefert als Kontext-Hinweis das ASN-Team im Büro des eingeloggten Nutzers. Teams und
 * Assistenten kommen aus dem öffentlichen flzplaner-V1-Katalog für die Areas des
 * Nutzers (Büro = Area); ohne Session, gültige Definition, Area oder planer bleibt
 * das Ergebnis leer (fail-closed, keine Rechte).
 */
final class AssistantContextListener implements IEventListener
{
    public function __construct(
        private IUserSession $session,
        private IGroupManager $groups,
        private OrganizationSettingsService $settings,
        private PlanerTeamCatalogGateway $catalog,
    )
    {
    }

    public function handle(Event $event): void
    {
        if (!$event instanceof AssistantContextQueryEvent)
        {
            return;
        }
        $user = $this->session->getUser();
        if ($user === null)
        {
            return;
        }
        $state = $this->settings->state();
        if (!$state['valid'])
        {
            return;
        }
        $definition = $state['definition'];
        $areaKeys = $definition->areaKeysForGroups(array_map('strval', $this->groups->getUserGroupIds($user)));
        if ($areaKeys === [])
        {
            return;
        }
        $wanted = array_fill_keys($event->employeeUids(), true);
        foreach ($this->catalog->teamsForAreas($areaKeys) as $team)
        {
            foreach ($team->assistants() as $assistant)
            {
                $uid = (string)($assistant['uid'] ?? '');
                if ($uid !== '' && isset($wanted[$uid]))
                {
                    $event->add(new AssistantContext($uid, team: $team->displayName()));
                }
            }
        }
    }
}
