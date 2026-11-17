<?php

declare(strict_types=1);

use OCA\FlzOrganization\Permission\PermissionProvider;
use OCA\FlzPermissionMatrix\PublicApi\V1\RegisterPermissionProvidersEvent;

$event = new RegisterPermissionProvidersEvent();
\OC::$server->getEventDispatcher()->dispatchTyped($event);

$provider = $event->providers()['flz_organization'] ?? null;
if (!$provider instanceof PermissionProvider)
{
    throw new RuntimeException('flz_organization did not register its V1 permission provider.');
}

if ($event->registrationFailures() !== [])
{
    throw new RuntimeException('The V1 permission-provider registry reported a registration failure.');
}
