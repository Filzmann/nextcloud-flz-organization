<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\PublicApi\V1;

use DateTimeZone;
use InvalidArgumentException;

final class CalendarContextSnapshot {
    public function __construct(
        private string $countryCode,
        private string $subdivisionCode,
        private string $timezone,
    ) {
        if (preg_match('/^[A-Z]{2}$/', $countryCode) !== 1
            || preg_match('/^' . preg_quote($countryCode, '/') . '-[A-Z0-9]{1,3}$/', $subdivisionCode) !== 1
            || !in_array($timezone, DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC), true)) {
            throw new InvalidArgumentException('Invalid public calendar context.');
        }
    }

    public function contractVersion(): string { return Contract::VERSION; }
    public function countryCode(): string { return $this->countryCode; }
    public function subdivisionCode(): string { return $this->subdivisionCode; }
    public function timezone(): string { return $this->timezone; }

    /** @return array{contractVersion:string,countryCode:string,subdivisionCode:string,timezone:string} */
    public function toArray(): array {
        return [
            'contractVersion' => Contract::VERSION,
            'countryCode' => $this->countryCode,
            'subdivisionCode' => $this->subdivisionCode,
            'timezone' => $this->timezone,
        ];
    }
}

