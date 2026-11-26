<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\Service;

use OCP\App\IAppManager;
use OCP\Server;

/**
 * Liest ASN-Teams inklusive Assistenten aus dem öffentlichen flzplaner-V1-Katalog
 * (AcuteShiftCatalogProvider), fail-closed: ohne aktivierten flzplaner, ohne
 * kompatiblem Vertrag oder bei Fehlern bleibt das Ergebnis leer.
 */
class PlanerTeamCatalogGateway
{
    public function __construct(private IAppManager $apps)
    {
    }

    /**
     * @param list<string> $areaKeys Büro-Area-Schlüssel (z. B. 'west') der abgefragten Büros
     * @return list<\OCA\FlzPlaner\PublicApi\V1\AcuteShiftTeam>
     */
    public function teamsForAreas(array $areaKeys): array
    {
        if ($areaKeys === [])
        {
            return [];
        }
        if (!in_array('flzplaner', $this->apps->getEnabledApps(), true))
        {
            return [];
        }
        $class = 'OCA\\FlzPlaner\\PublicApi\\V1\\AcuteShiftCatalogProvider';
        if (!class_exists($class))
        {
            return [];
        }
        try
        {
            $teams = Server::get($class)->teamsForBureaux($areaKeys);
        }
        catch (\Throwable)
        {
            return [];
        }
        $out = [];
        foreach ($teams as $team)
        {
            if ($team->contractVersion() === '1.0')
            {
                $out[] = $team;
            }
        }
        return $out;
    }
}
