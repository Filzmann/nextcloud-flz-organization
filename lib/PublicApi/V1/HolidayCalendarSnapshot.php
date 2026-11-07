<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\PublicApi\V1;

use InvalidArgumentException;
use OCA\FlzOrganization\Calendar\HolidayCalendar;

/** Datensparsame, unveränderliche Feiertagsprojektion für Consumer. */
final class HolidayCalendarSnapshot {
    public const CONTRACT_VERSION = '1.0';

    /**
     * @param list<string> $publicHolidayDates
     * @param list<array{startDate:string,endDate:string,name:string}> $publicHolidays
     */
    public function __construct(
        private int $year,
        private string $timezone,
        private string $cacheStatus,
        private array $publicHolidayDates,
        private ?string $fetchedAt,
        private array $publicHolidays = [],
    ) {
        if ($year < 2000 || $year > 2100) throw new InvalidArgumentException('Invalid holiday year.');
        if ($timezone === '') throw new InvalidArgumentException('Missing holiday timezone.');
        if (!in_array($cacheStatus, ['fresh', 'current', 'stale', 'unavailable'], true)) throw new InvalidArgumentException('Invalid holiday cache status.');
        foreach ($publicHolidayDates as $date) {
            if (!is_string($date) || preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1) throw new InvalidArgumentException('Invalid holiday date.');
        }
        $this->publicHolidayDates = array_values(array_unique($publicHolidayDates));
        sort($this->publicHolidayDates);
        $this->publicHolidays = array_map(static function (mixed $period): array {
            if (!is_array($period)) throw new InvalidArgumentException('Invalid public holiday period.');
            $startDate = $period['startDate'] ?? null;
            $endDate = $period['endDate'] ?? null;
            $name = $period['name'] ?? null;
            if (!is_string($startDate) || !is_string($endDate) || !is_string($name)
                || preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) !== 1
                || preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate) !== 1
                || trim($name) === '' || $endDate < $startDate) {
                throw new InvalidArgumentException('Invalid public holiday period.');
            }
            return ['startDate' => $startDate, 'endDate' => $endDate, 'name' => trim($name)];
        }, $publicHolidays);
        if ($cacheStatus === 'unavailable') {
            $this->publicHolidayDates = [];
            $this->publicHolidays = [];
        }
    }

    public static function fromCalendar(HolidayCalendar $calendar): self {
        $data = $calendar->toArray();
        $dates = [];
        foreach ($data['publicHolidays'] as $period) {
            $start = new \DateTimeImmutable($period['startDate']);
            $end = new \DateTimeImmutable($period['endDate']);
            for ($date = $start; $date <= $end; $date = $date->modify('+1 day')) $dates[] = $date->format('Y-m-d');
        }
        $periods = array_map(static fn(array $period): array => [
            'startDate' => (string)$period['startDate'],
            'endDate' => (string)$period['endDate'],
            'name' => (string)$period['name'],
        ], $data['publicHolidays']);
        return new self((int)$data['year'], (string)$data['context']['timezone'], (string)$data['cacheStatus'], $dates, $data['fetchedAt'], $periods);
    }

    public function contractVersion(): string { return self::CONTRACT_VERSION; }
    public function year(): int { return $this->year; }
    public function timezone(): string { return $this->timezone; }
    public function cacheStatus(): string { return $this->cacheStatus; }
    /** @return list<string> */
    public function publicHolidayDates(): array { return $this->publicHolidayDates; }
    /** @return list<array{startDate:string,endDate:string,name:string}> */
    public function publicHolidays(): array { return $this->publicHolidays; }
    public function fetchedAt(): ?string { return $this->fetchedAt; }
    public function isUsable(): bool { return $this->cacheStatus !== 'unavailable'; }

    /** @return array{contractVersion:string,year:int,timezone:string,cacheStatus:string,publicHolidayDates:list<string>,publicHolidays:list<array{startDate:string,endDate:string,name:string}>,fetchedAt:?string} */
    public function toArray(): array {
        return [
            'contractVersion' => self::CONTRACT_VERSION,
            'year' => $this->year,
            'timezone' => $this->timezone,
            'cacheStatus' => $this->cacheStatus,
            'publicHolidayDates' => $this->publicHolidayDates,
            'publicHolidays' => $this->publicHolidays,
            'fetchedAt' => $this->fetchedAt,
        ];
    }
}
