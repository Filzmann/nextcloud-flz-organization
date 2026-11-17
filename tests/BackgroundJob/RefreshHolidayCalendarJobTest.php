<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\Tests\BackgroundJob;

use OCA\FlzOrganization\BackgroundJob\RefreshHolidayCalendarJob;
use OCA\FlzOrganization\Calendar\CalendarContextSettingsService;
use OCA\FlzOrganization\Calendar\HolidayCalendarCacheStore;
use OCA\FlzOrganization\Calendar\HolidayCalendarService;
use OCA\FlzOrganization\Calendar\OpenHolidaysClient;
use OCA\FlzOrganization\Tests\FakeAppConfig;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\IJob;
use OCP\BackgroundJob\TimedJob;
use OCP\Http\Client\IClient;
use OCP\Http\Client\IClientService;
use OCP\Http\Client\IResponse;
use Psr\Log\LoggerInterface;
use ReflectionMethod;
use Stringable;

// Fachliche Invariante: Der RefreshHolidayCalendarJob ist ein täglicher,
// nicht parallelisierbarer TimedJob und aktualisiert bei jedem Lauf das
// laufende sowie die zwei folgenden Kalenderjahre im gemeinsamen Cache.
// Ebene: Job-Test mit echtem HolidayCalendarService, aufzeichnendem
// HTTP-Fake und FakeAppConfig-Cache. Beweist: Job-Konfiguration und die
// tatsächlich abgerufenen Jahre. Beweist nicht: Nextcloud-Job-Queue oder
// Cron-Ausführung.

final class FakeTimeFactory implements ITimeFactory
{
    public function __construct(private int $time)
    {
    }

    public function getTime(): int
    {
        return $this->time;
    }
}

final class FakeHttpResponse implements IResponse
{
    public function getStatusCode(): int
    {
        return 200;
    }

    public function getBody(): mixed
    {
        return '[]';
    }
}

/** Zeichnet jede abgefragte URL auf, damit die abgerufenen Jahre prüfbar sind. */
final class RecordingHttpClient implements IClient
{
    /** @var list<string> */
    public array $requestedUrls = [];

    public function get(string $url, array $options = []): IResponse
    {
        $this->requestedUrls[] = $url;
        return new FakeHttpResponse();
    }
}

final class FakeClientService implements IClientService
{
    public function __construct(private IClient $client)
    {
    }

    public function newClient(): IClient
    {
        return $this->client;
    }
}

final class NullLogger implements LoggerInterface
{
    public function emergency(string|Stringable $message, array $context = []): void {}
    public function alert(string|Stringable $message, array $context = []): void {}
    public function critical(string|Stringable $message, array $context = []): void {}
    public function error(string|Stringable $message, array $context = []): void {}
    public function warning(string|Stringable $message, array $context = []): void {}
    public function notice(string|Stringable $message, array $context = []): void {}
    public function info(string|Stringable $message, array $context = []): void {}
    public function debug(string|Stringable $message, array $context = []): void {}
    public function log($level, string|Stringable $message, array $context = []): void {}
}

// Festes Datum: 15.06.2026 12:00 UTC, in Europe/Berlin ebenfalls 2026.
$time = new FakeTimeFactory(strtotime('2026-06-15 12:00:00 UTC'));
$config = new FakeAppConfig();
$contexts = new CalendarContextSettingsService($config);
$httpClient = new RecordingHttpClient();
$cacheStore = new HolidayCalendarCacheStore($config);
$holidays = new HolidayCalendarService(
    new OpenHolidaysClient(new FakeClientService($httpClient)),
    $cacheStore,
    $contexts,
    $time,
    new NullLogger(),
);
$job = new RefreshHolidayCalendarJob($time, $contexts, $holidays);

// Test 1: Der Job ist ein TimedJob und implementiert den IJob-Vertrag.
assertTrue($job instanceof TimedJob, 'The job must be a TimedJob.');
assertTrue($job instanceof IJob, 'The job must implement IJob.');

// Test 2: Der Job läuft täglich, ist zeitunsensibel und nicht parallelisierbar.
assertSame(24 * 3600, $job->getInterval(), 'The job must run once per day.');
assertSame(IJob::TIME_INSENSITIVE, $job->getTimeSensitivity(), 'The job must be time-insensitive.');
assertSame(false, $job->getAllowParallelRuns(), 'The job must not allow parallel runs.');

// Test 3 (positiv): Ein Lauf aktualisiert das laufende und die zwei folgenden Jahre.
$run = new ReflectionMethod($job, 'run');
$run->setAccessible(true);
$run->invoke($job, null);

$requestedYears = [];
foreach ($httpClient->requestedUrls as $url)
{
    parse_str((string)parse_url($url, PHP_URL_QUERY), $query);
    if (isset($query['validFrom']) && preg_match('/^(\d{4})-01-01$/', (string)$query['validFrom'], $matches) === 1)
    {
        $requestedYears[(int)$matches[1]] = true;
    }
}
assertSame([2026, 2027, 2028], array_keys($requestedYears), 'One run must refresh the current year and the two following years.');

// Test 4 (negativ/Grenzfall): Jahre außerhalb 2000–2100 werden abgelehnt und ändern nichts.
// Snapshot des Request-Recorders und des Caches VOR dem abgelehnten Aufruf.
$urlsBefore = $httpClient->requestedUrls;
assertThrows(
    static fn() => $holidays->forYear(1999, true),
    \InvalidArgumentException::class,
    'Years outside the supported range must be rejected.'
);
// Die abgelehnte Jahreszahl darf weder einen Provider-Request auslösen noch den Cache verändern.
assertSame(count($urlsBefore), count($httpClient->requestedUrls), 'A rejected year must not trigger any provider request.');
$requestedYearsAfter = [];
foreach ($httpClient->requestedUrls as $url)
{
    parse_str((string)parse_url($url, PHP_URL_QUERY), $query);
    if (isset($query['validFrom']) && preg_match('/^(\d{4})-01-01$/', (string)$query['validFrom'], $matches) === 1)
    {
        $requestedYearsAfter[(int)$matches[1]] = true;
    }
}
assertSame(array_keys($requestedYears), array_keys($requestedYearsAfter), 'The requested years must be unchanged after a rejected year.');
assertSame(null, $cacheStore->get(1999, $contexts->context()), 'A rejected year must not gain a cache entry.');
