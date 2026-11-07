<?php

declare(strict_types=1);

use OCA\FlzOrganization\Calendar\CalendarContext;
use OCA\FlzOrganization\Calendar\CalendarContextSettingsService;
use OCA\FlzOrganization\PublicApi\V1\CalendarContextProvider;
use OCA\FlzOrganization\Tests\FakeAppConfig;

$config = new FakeAppConfig();
$settings = new CalendarContextSettingsService($config);
$provider = new CalendarContextProvider($settings);

assertSame('Europe/Berlin', $provider->context()->timezone(), 'Fresh installs must use the canonical Berlin display timezone.');
$saved = $settings->save([
    'version' => CalendarContext::VERSION,
    'countryCode' => 'DE',
    'subdivisionCode' => 'DE-BY',
    'timezone' => 'Europe/Berlin',
]);
assertSame('DE-BY', $saved->subdivisionCode(), 'Only validated calendar contexts may be persisted.');
assertSame('DE-BY', $provider->context()->subdivisionCode(), 'The public context must project the provider-owned source.');
assertThrows(
    static fn(): CalendarContext => $settings->save(['version' => CalendarContext::VERSION, 'countryCode' => 'D1', 'subdivisionCode' => 'DE-BY', 'timezone' => 'Europe/Berlin']),
    InvalidArgumentException::class,
    'Malformed country codes must be rejected without mutation.',
);

