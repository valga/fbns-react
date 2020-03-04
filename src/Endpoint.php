<?php

declare(strict_types=1);

namespace Fbns\Client;

interface Endpoint
{
    public function appId(): int;

    public function capabilities(): int;

    public function noAutomaticForeground(): bool;

    public function makeUserAvailableInForeground(): bool;

    public function isInitiallyForeground(): bool;

    public function subscribeTopics(): ?array;

    public function loggerUserId(): ?int;

    public function appSpecificInfo(): ?array;

    public function regionPreference(): ?string;
}
