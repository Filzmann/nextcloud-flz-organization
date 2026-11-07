<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\BackgroundJob;

use DateTimeImmutable;
use OCA\FlzOrganization\Calendar\CalendarContextSettingsService;
use OCA\FlzOrganization\Calendar\HolidayCalendarService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\IJob;
use OCP\BackgroundJob\TimedJob;
use Override;

/** Aktualisiert täglich das aktuelle und die zwei folgenden Jahre des gemeinsamen Kalendercaches. */
final class RefreshHolidayCalendarJob extends TimedJob {
    public function __construct(
        private ITimeFactory $clock,
        private CalendarContextSettingsService $contexts,
        private HolidayCalendarService $holidays,
    ) {
        parent::__construct($clock);
        $this->setInterval(24 * 3600);
        $this->setTimeSensitivity(IJob::TIME_INSENSITIVE);
        $this->setAllowParallelRuns(false);
    }

    #[Override]
    protected function run($argument): void {
        $year = (int)(new DateTimeImmutable('@' . $this->clock->getTime()))
            ->setTimezone($this->contexts->context()->timezone())
            ->format('Y');
        for ($offset = 0; $offset <= 2; $offset++) $this->holidays->forYear($year + $offset, true);
    }
}
