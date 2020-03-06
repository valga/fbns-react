<?php

declare(strict_types=1);

namespace Fbns;

interface Endpoint
{
    /**
     * @return int|string
     */
    public function appId();

    /**
     * @return int|string
     */
    public function capabilities();

    public function noAutomaticForeground(): bool;

    public function makeUserAvailableInForeground(): bool;

    public function isInitiallyForeground(): bool;

    public function subscribeTopics(): ?array;

    /**
     * @return int|string|null
     */
    public function loggerUserId();

    public function appSpecificInfo(): ?array;

    public function regionPreference(): ?string;
}
