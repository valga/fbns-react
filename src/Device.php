<?php

declare(strict_types=1);

namespace Fbns;

interface Device
{
    /**
     * Get user-agent string in FB format.
     */
    public function userAgent(): string;

    /**
     * Get device uptime in milliseconds.
     *
     * @return int|string
     */
    public function uptime();
}
