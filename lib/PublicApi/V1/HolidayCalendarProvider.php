<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\PublicApi\V1;

use OCA\FlzOrganization\Calendar\HolidayCalendarService;

final class HolidayCalendarProvider {
    public function __construct(private HolidayCalendarService $calendars) {}

    public function calendar(int $year): HolidayCalendarSnapshot {
        return HolidayCalendarSnapshot::fromCalendar($this->calendars->forYear($year));
    }
}

