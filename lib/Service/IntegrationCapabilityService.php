<?php

declare(strict_types=1);

namespace OCA\FlzOrganization\Service;

use OCA\FlzOrganization\PublicApi\V1\IntegrationCapabilityQueryEvent;
use OCP\EventDispatcher\IEventDispatcher;

final class IntegrationCapabilityService {
    public function __construct(private IEventDispatcher $events) {}

    /** @param list<string> $capabilities @return array<string,list<string>> */
    public function query(array $capabilities): array {
        $event = new IntegrationCapabilityQueryEvent($capabilities);
        $this->events->dispatchTyped($event);
        return $event->available();
    }
}

