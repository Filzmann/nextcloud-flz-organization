<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\Calendar;

use OCA\FlzOrganization\AppInfo\Application;
use OCP\IAppConfig;

/** Owns the organization-wide calendar context in this app's native AppConfig. */
final class CalendarContextSettingsService {
    public const CONFIG_KEY = 'calendar_context';

    public function __construct(private IAppConfig $config) {}

    public function context(): CalendarContext {
        $raw = $this->config->getValueString(Application::APP_ID, self::CONFIG_KEY, '');
        if ($raw === '') {
            return CalendarContext::defaults();
        }
        try {
            $data = json_decode($raw, true, 16, JSON_THROW_ON_ERROR);
            return CalendarContext::get(is_array($data) ? $data : []);
        } catch (\Throwable) {
            return CalendarContext::defaults();
        }
    }

    public function save(array $data): CalendarContext {
        $context = CalendarContext::get($data);
        $this->config->setValueString(
            Application::APP_ID,
            self::CONFIG_KEY,
            json_encode($context->toArray(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
        );
        return $context;
    }
}

