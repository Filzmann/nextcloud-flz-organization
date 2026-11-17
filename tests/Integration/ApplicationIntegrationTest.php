<?php

declare(strict_types=1);

use OCA\FlzOrganization\AppInfo\Application;
use OCA\FlzOrganization\Permission\PermissionProviderListener;
use OCA\FlzPermissionMatrix\PublicApi\V1\RegisterPermissionProvidersEvent;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

$context = new class implements IRegistrationContext
{
    /** @var array<string, string> */
    public array $aliases = [];

    /** @var array<string, string> */
    public array $listeners = [];

    public function registerServiceAlias(string $alias, string $target): void
    {
        $this->aliases[$alias] = $target;
    }

    public function registerEventListener(string $event, string $listener, int $priority = 0): void
    {
        $this->listeners[$event] = $listener;
    }
};

$application = new Application();
$application->register($context);

assertSame(
    PermissionProviderListener::class,
    $context->listeners[RegisterPermissionProvidersEvent::class] ?? null,
    'The permission provider must be registered lazily through the supported Nextcloud event-listener bootstrap API.',
);
assertSame(
    [],
    $context->aliases,
    'The application bootstrap must not register a service alias with a factory as its target.',
);
assertTrue(
    !isset($context->listeners['OCA\\FlzDataProtection\\PublicApi\\V1\\RegisterPersonalDataProvidersEvent']),
    'An app without app-owned personal data must not register a synthetic PersonalDataProvider.',
);
