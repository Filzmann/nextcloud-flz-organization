<?php

declare(strict_types=1);

use OCA\FlzOrganization\Calendar\CalendarContext;
use OCA\FlzOrganization\Calendar\CalendarContextSettingsService;
use OCA\FlzOrganization\PublicApi\V1\CalendarContextProvider;
use OCA\FlzOrganization\Tests\FakeAppConfig;
use OCA\FlzOrganization\Tests\FakeSystemConfig;

$config = new FakeAppConfig();
$system = new FakeSystemConfig(['timezone' => 'Europe/Vienna']);
$settings = new CalendarContextSettingsService($config, $system);
$provider = new CalendarContextProvider($settings);

assertSame('Europe/Vienna', $provider->context()->timezone(), 'Fresh installs must use the instance timezone from the Nextcloud system settings, not a hardcoded default.');
$fallback = new CalendarContextSettingsService(new FakeAppConfig(), new FakeSystemConfig([]));
assertSame('UTC', $fallback->context()->timezone()->getName(), 'Without a configured instance timezone the context must fall back to UTC.');
$saved = $settings->save([
    'version' => CalendarContext::VERSION,
    'countryCode' => 'DE',
    'subdivisionCode' => 'DE-BY',
    'timezone' => 'Europe/Berlin',
]);
assertSame('DE-BY', $saved->subdivisionCode(), 'Only validated calendar contexts may be persisted.');
assertSame('Europe/Vienna', $saved->timezone()->getName(), 'The stored timezone must always be the instance timezone, even if another one is submitted.');
assertSame('DE-BY', $provider->context()->subdivisionCode(), 'The public context must project the provider-owned source.');
assertThrows(
    static fn(): CalendarContext => $settings->save(['version' => CalendarContext::VERSION, 'countryCode' => 'D1', 'subdivisionCode' => 'DE-BY', 'timezone' => 'Europe/Berlin']),
    InvalidArgumentException::class,
    'Malformed country codes must be rejected without mutation.',
);

