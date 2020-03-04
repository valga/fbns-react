<?php

declare(strict_types=1);

namespace Fbns\Client;

interface Device
{
    /**
     * Get user-agent string in FB format.
     */
    public function userAgent(): string;

    /**
     * Get device uptime in milliseconds.
     */
    public function uptime(): int;
}
