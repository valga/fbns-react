<?php

declare(strict_types=1);

namespace Fbns\Endpoint;

use Fbns\Endpoint;

class PushEndpoint implements Endpoint
{
    private const APP_ID = 567310203415052;
    private const CAPABILITIES = 128;

    public function appId(): int
    {
        return self::APP_ID;
    }

    public function capabilities(): int
    {
        return self::CAPABILITIES;
    }

    public function noAutomaticForeground(): bool
    {
        return true;
    }

    public function makeUserAvailableInForeground(): bool
    {
        return false;
    }

    public function isInitiallyForeground(): bool
    {
        return false;
    }

    public function subscribeTopics(): ?array
    {
        return [76, 80, 231];
    }

    public function loggerUserId(): ?int
    {
        return -1;
    }

    public function appSpecificInfo(): ?array
    {
        return null;
    }

    public function regionPreference(): ?string
    {
        return null;
    }
}
