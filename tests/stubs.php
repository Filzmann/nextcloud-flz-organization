<?php

declare(strict_types=1);

namespace OCP {
    interface IAppConfig {
        public function getValueString(string $appId, string $key, string $default = '', bool $lazy = false): string;
        public function setValueString(string $appId, string $key, string $value, bool $lazy = false, int $sensitive = 0): bool;
    }
}

namespace OCP\EventDispatcher {
    class Event { public function __construct() {} }
    interface IEventDispatcher {
        public function dispatchTyped(Event $event): Event;
    }
}

namespace OCP\AppFramework\Utility {
    interface ITimeFactory { public function getTime(): int; }
}

namespace OCP\AppFramework {
    class App { public function __construct(string $appId) {} }
}

namespace Psr\Log {
    interface LoggerInterface {
        public function emergency(string|\Stringable $message, array $context = []): void;
        public function alert(string|\Stringable $message, array $context = []): void;
        public function critical(string|\Stringable $message, array $context = []): void;
        public function error(string|\Stringable $message, array $context = []): void;
        public function warning(string|\Stringable $message, array $context = []): void;
        public function notice(string|\Stringable $message, array $context = []): void;
        public function info(string|\Stringable $message, array $context = []): void;
        public function debug(string|\Stringable $message, array $context = []): void;
        public function log($level, string|\Stringable $message, array $context = []): void;
    }
}

