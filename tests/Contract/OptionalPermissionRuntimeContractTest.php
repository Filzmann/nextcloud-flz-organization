<?php

declare(strict_types=1);

namespace OCP\AppFramework {
    class App
    {
        public function __construct(string $appId, array $urlParams = [])
        {
        }
    }
}

namespace OCP\AppFramework\Bootstrap {
    interface IBootContext
    {
    }

    interface IRegistrationContext
    {
        public function registerEventListener(string $event, string $listener, int $priority = 0): void;
    }

    interface IBootstrap
    {
        public function register(IRegistrationContext $context): void;

        public function boot(IBootContext $context): void;
    }
}

namespace {
    use OCA\FlzOrganization\AppInfo\Application;
    use OCP\AppFramework\Bootstrap\IRegistrationContext;

    require_once dirname(__DIR__) . '/bootstrap.php';

    $context = new class implements IRegistrationContext
    {
        /** @var array<string, string> */
        public array $listeners = [];

        public function registerEventListener(string $event, string $listener, int $priority = 0): void
        {
            $this->listeners[$event] = $listener;
        }
    };

    (new Application())->register($context);

    $event = 'OCA\\FlzPermissionMatrix\\PublicApi\\V1\\RegisterPermissionProvidersEvent';
    $listener = 'OCA\\FlzOrganization\\Permission\\PermissionProviderListener';
    if (($context->listeners[$event] ?? null) !== $listener)
    {
        throw new RuntimeException('The optional permission listener was not registered by class name.');
    }
    if (interface_exists('OCA\\FlzPermissionMatrix\\PublicApi\\V1\\PermissionProvider', false))
    {
        throw new RuntimeException('The isolated bootstrap unexpectedly loaded the optional provider contract.');
    }
}
