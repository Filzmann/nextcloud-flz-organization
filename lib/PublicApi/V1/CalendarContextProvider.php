<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\PublicApi\V1;

use OCA\FlzOrganization\Calendar\CalendarContextSettingsService;

final class CalendarContextProvider {
    public function __construct(private CalendarContextSettingsService $contexts) {}

    public function context(): CalendarContextSnapshot {
        $data = $this->contexts->context()->toArray();
        return new CalendarContextSnapshot($data['countryCode'], $data['subdivisionCode'], $data['timezone']);
    }
}

