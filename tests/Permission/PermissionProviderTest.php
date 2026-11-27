<?php

declare(strict_types=1);

use OCA\FlzOrganization\Permission\PermissionProvider;
use OCA\FlzOrganization\Permission\PermissionProviderListener;
use OCA\FlzPermissionMatrix\PublicApi\V1\RegisterPermissionProvidersEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;

$provider = new PermissionProvider();
$descriptor = $provider->descriptor();

assertSame('flz_organization', $descriptor->appId(), 'The provider app ID must remain stable.');
assertSame('Organisationsdienste', $descriptor->displayName(), 'The provider display name must remain stable.');
assertSame('1.0', $descriptor->contractVersion(), 'The provider must advertise the supported V1 contract.');
assertSame(['permissions'], $descriptor->capabilities(), 'The provider must expose only the permission-inventory capability.');

$result = $provider->collect();
assertSame([], $result->rules(), 'The app must not invent user-grantable permission rules.');
assertTrue($result->complete(), 'The empty permission inventory is complete for the current app scope.');
assertSame([], $result->warnings(), 'A deliberately empty permission inventory must not be reported as an unknown failure.');

$listener = new PermissionProviderListener($provider);
assertTrue($listener instanceof IEventListener, 'The provider listener must implement the native Nextcloud event-listener contract.');

$event = new RegisterPermissionProvidersEvent();
$listener->handle($event);
assertSame(
    $provider,
    $event->providers()['flz_organization'] ?? null,
    'A compatible permission registry event must receive the provider exactly once.',
);
assertSame([], $event->registrationFailures(), 'The compatible provider must not produce a registration failure.');

$listener->handle(new Event());
assertSame(
    ['flz_organization'],
    array_keys($event->providers()),
    'An unrelated event must be ignored without registering another provider or changing state.',
);
