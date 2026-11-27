<?php

declare(strict_types=1);

namespace OCP {
    interface IRequest {
    }

    interface IAppConfig {
        public function getValueString(string $appId, string $key, string $default = '', bool $lazy = false): string;
        public function setValueString(string $appId, string $key, string $value, bool $lazy = false, int $sensitive = 0): bool;
    }

    interface IConfig {
        public function getSystemValueString(string $key, string $default = ''): string;
    }
}

namespace OCP\EventDispatcher {
    class Event
    {
        public function __construct()
        {
        }
    }

    interface IEventListener
    {
        public function handle(Event $event): void;
    }

    interface IEventDispatcher
    {
        public function dispatchTyped(Event $event): Event;
    }
}

namespace OCP\AppFramework\Utility {
    interface ITimeFactory { public function getTime(): int; }
}

namespace OCP\AppFramework {
    class App
    {
        public function __construct(string $appId, array $urlParams = [])
        {
        }
    }

    class Controller
    {
        protected string $appName;

        public function __construct(string $appName, \OCP\IRequest $request)
        {
            $this->appName = $appName;
        }
    }
}

namespace OCP\AppFramework\Http {
    class TemplateResponse
    {
        public const RENDER_AS_GUEST = 'guest';
        public const RENDER_AS_USER = 'user';

        public function __construct(
            private string $appId,
            private string $templateName,
            private array $params = [],
            private string $renderAs = self::RENDER_AS_USER,
        )
        {
        }

        public function getApp(): string { return $this->appId; }

        public function getTemplateName(): string { return $this->templateName; }

        public function getParams(): array { return $this->params; }

        public function getRenderAs(): string { return $this->renderAs; }
    }
}

namespace OCP\BackgroundJob {
    use OCP\AppFramework\Utility\ITimeFactory;

    interface IJob
    {
        public const TIME_SENSITIVE = 0;
        public const TIME_INSENSITIVE = 1;
    }

    abstract class Job implements IJob
    {
        abstract protected function run($argument): void;
    }

    abstract class TimedJob extends Job
    {
        private int $interval = 0;
        private int $timeSensitivity = IJob::TIME_SENSITIVE;
        private bool $allowParallelRuns = true;

        public function __construct(protected ITimeFactory $time)
        {
        }

        public function setInterval(int $interval): void { $this->interval = $interval; }

        public function setTimeSensitivity(int $sensitivity): void { $this->timeSensitivity = $sensitivity; }

        public function setAllowParallelRuns(bool $allow): void { $this->allowParallelRuns = $allow; }

        public function getInterval(): int { return $this->interval; }

        public function getTimeSensitivity(): int { return $this->timeSensitivity; }

        public function getAllowParallelRuns(): bool { return $this->allowParallelRuns; }
    }
}

namespace OCP\Http\Client {
    interface IResponse
    {
        public function getStatusCode(): int;

        public function getBody(): mixed;
    }

    interface IClient
    {
        public function get(string $url, array $options = []): IResponse;
    }

    interface IClientService
    {
        public function newClient(): IClient;
    }
}

namespace OCP\AppFramework\Bootstrap {
    interface IBootContext
    {
    }

    interface IRegistrationContext
    {
        public function registerServiceAlias(string $alias, string $target): void;

        public function registerEventListener(string $event, string $listener, int $priority = 0): void;
    }

    interface IBootstrap
    {
        public function register(IRegistrationContext $context): void;

        public function boot(IBootContext $context): void;
    }
}

namespace OCA\FlzPermissionMatrix\PublicApi\V1 {
    use InvalidArgumentException;
    use OCP\EventDispatcher\Event;
    use Throwable;

    interface PermissionProvider
    {
        public function descriptor(): PermissionProviderDescriptor;

        public function collect(): PermissionProviderResult;
    }

    final class PermissionProviderDescriptor
    {
        public function __construct(
            private string $appId,
            private string $displayName,
            private string $contractVersion,
            private array $capabilities,
        )
        {
            if (
                !preg_match('/^[a-z][a-z0-9_]{1,63}$/', $appId)
                || trim($displayName) === ''
                || !preg_match('/^[1-9][0-9]*\.[0-9]+$/', $contractVersion)
                || $capabilities === []
            )
            {
                throw new InvalidArgumentException('Invalid provider descriptor.');
            }
        }

        public function appId(): string
        {
            return $this->appId;
        }

        public function displayName(): string
        {
            return $this->displayName;
        }

        public function contractVersion(): string
        {
            return $this->contractVersion;
        }

        public function capabilities(): array
        {
            return $this->capabilities;
        }
    }

    final class PermissionProviderResult
    {
        public function __construct(
            private array $rules,
            private bool $complete = true,
            private array $warnings = [],
        )
        {
        }

        public function rules(): array
        {
            return $this->rules;
        }

        public function complete(): bool
        {
            return $this->complete;
        }

        public function warnings(): array
        {
            return $this->warnings;
        }
    }

    final class RegisterPermissionProvidersEvent extends Event
    {
        public const CONTRACT_VERSION = '1.0';

        /** @var array<string, PermissionProvider> */
        private array $providers = [];

        /** @var array<string, string> */
        private array $registrationFailures = [];

        public function register(PermissionProvider $provider): void
        {
            try {
                $descriptor = $provider->descriptor();
                $appId = $descriptor->appId();
                if (
                    $descriptor->contractVersion() !== self::CONTRACT_VERSION
                    || !in_array('permissions', $descriptor->capabilities(), true)
                    || isset($this->providers[$appId])
                )
                {
                    $this->registrationFailures[$appId] = 'Provider incompatible.';
                    unset($this->providers[$appId]);
                    return;
                }
                $this->providers[$appId] = $provider;
            }
            catch (Throwable)
            {
                $this->registrationFailures[$provider::class] = 'Provider incompatible.';
            }
        }

        /** @return array<string, PermissionProvider> */
        public function providers(): array
        {
            return $this->providers;
        }

        /** @return array<string, string> */
        public function registrationFailures(): array
        {
            return $this->registrationFailures;
        }
    }
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

