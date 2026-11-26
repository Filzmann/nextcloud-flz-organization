<?php

declare(strict_types=1);

namespace OCP {
    if (!interface_exists(IUser::class)) { interface IUser { public function getUID(): string; } }
    if (!interface_exists(IUserSession::class)) { interface IUserSession { public function getUser(): ?IUser; } }
    if (!interface_exists(IGroupManager::class)) { interface IGroupManager { /** @return list<string> */ public function getUserGroupIds(IUser $user): array; } }
}

namespace OCP\App {
    if (!interface_exists(IAppManager::class)) { interface IAppManager { /** @return list<string> */ public function getEnabledApps(): array; } }
}

namespace {

// Cross-App-Vertragsladepfad nur für Tests (wie flz_akut-Consumer-Tests): kapazitaet-V1-Event + planer-V1-Team
spl_autoload_register(static function (string $class): void {
    foreach (['OCA\\FlzKapazitaet\\' => '/flz_kapazitaet/lib/', 'OCA\\FlzPlaner\\' => '/flzplaner/lib/'] as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $path = dirname(__DIR__, 3) . $dir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($path)) require_once $path;
        }
    }
});

use OCA\FlzKapazitaet\PublicApi\V1\AssistantContextQueryEvent;
use OCA\FlzOrganization\Listener\AssistantContextListener;
use OCA\FlzOrganization\Organization\OrganizationDefinition;
use OCA\FlzOrganization\Organization\OrganizationSettingsService;
use OCA\FlzOrganization\Service\PlanerTeamCatalogGateway;
use OCA\FlzOrganization\Tests\FakeAppConfig;
use OCA\FlzPlaner\PublicApi\V1\AcuteShiftTeam;
use OCP\App\IAppManager;
use OCP\EventDispatcher\Event;
use OCP\IGroupManager;
use OCP\IUser;
use OCP\IUserSession;

$user = new class implements IUser { public function getUID(): string { return 'office1'; } };
$session = new class($user) implements IUserSession {
    public function __construct(private ?IUser $user) {}
    public function getUser(): ?IUser { return $this->user; }
};
$groups = new class implements IGroupManager {
    /** @return list<string> */ public function getUserGroupIds(IUser $user): array { return ['Bereich-West']; }
};
$apps = new class implements IAppManager {
    /** @return list<string> */ public function getEnabledApps(): array { return ['flz_organization', 'flzplaner', 'flz_kapazitaet']; }
};

$config = new FakeAppConfig();
$settings = new OrganizationSettingsService($config);
$settings->save(OrganizationDefinition::defaults()->toArray());
$west = OrganizationDefinition::defaults()->areas()['west']['groupId'];
assertSame(['Bereich-West'], [$west], 'Test-Setup: Bereich-West');

$catalog = new class($apps) extends PlanerTeamCatalogGateway {
    public array $requestedAreas = [];
    public function teamsForAreas(array $areaKeys): array {
        $this->requestedAreas = $areaKeys;
        if ($areaKeys !== ['west']) return [];
        return [new AcuteShiftTeam('W1', 'Assistenzteam W1', 'west', [], [['uid' => 'alice', 'displayName' => 'Alice A'], ['uid' => 'bob', 'displayName' => 'Bob B']])];
    }
};

// Provider liefert Team-Hinweis für Assistenten des eigenen Büros
$listener = new AssistantContextListener($session, $groups, $settings, $catalog);
$event = new AssistantContextQueryEvent(['alice', 'bob', 'carl']);
$listener->handle($event);
$contexts = $event->contexts();
assertSame(['west'], $catalog->requestedAreas, 'Teams werden für die Areas des eingeloggten Nutzers abgefragt.');
assertSame(['entryDate' => null, 'dpa' => false, 'team' => 'Assistenzteam W1'], $contexts['alice']->toArray(), 'alice bekommt den Team-Hinweis.');
assertSame('Assistenzteam W1', $contexts['bob']->team(), 'bob bekommt den Team-Hinweis.');
assertTrue(!isset($contexts['carl']), 'carl ist in keinem Team des Büros -> kein Kontext.');

// Fremd-Event wird ignoriert
$listener->handle(new Event());

// Fail-closed: nicht eingeloggt -> keine Kontexte
$listenerNoUser = new AssistantContextListener(new class implements IUserSession { public function getUser(): ?IUser { return null; } }, $groups, $settings, $catalog);
$event2 = new AssistantContextQueryEvent(['alice']);
$listenerNoUser->handle($event2);
assertSame([], $event2->contexts(), 'Ohne Session darf kein Kontext geliefert werden.');

// Fail-closed: Nutzer ohne Area -> keine Kontexte
$groupsNoArea = new class implements IGroupManager { /** @return list<string> */ public function getUserGroupIds(IUser $user): array { return ['Buero']; } };
$listenerNoArea = new AssistantContextListener($session, $groupsNoArea, $settings, $catalog);
$event3 = new AssistantContextQueryEvent(['alice']);
$listenerNoArea->handle($event3);
assertSame([], $event3->contexts(), 'Ohne Büro-Area des Nutzers darf kein Kontext geliefert werden.');

// Fail-closed: ungültige Definition -> keine Kontexte
$configBroken = new FakeAppConfig();
$configBroken->inject(OrganizationSettingsService::CONFIG_KEY === '' ? 'x' : 'flz_organization', OrganizationSettingsService::CONFIG_KEY, '{broken');
$settingsBroken = new OrganizationSettingsService($configBroken);
$listenerBroken = new AssistantContextListener($session, $groups, $settingsBroken, $catalog);
$event4 = new AssistantContextQueryEvent(['alice']);
$listenerBroken->handle($event4);
assertSame([], $event4->contexts(), 'Ungültige Definition muss fail-closed ohne Kontexte liefern.');

echo "AssistantContextListenerTest: OK\n";
}
