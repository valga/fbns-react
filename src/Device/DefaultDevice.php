<?php

declare(strict_types=1);

namespace Fbns\Device;

use Fbns\Device;

class DefaultDevice implements Device
{
    /** @var string */
    private $userAgent;

    public function __construct(string $userAgent)
    {
        $this->userAgent = $userAgent;
    }

    public function userAgent(): string
    {
        return $this->userAgent;
    }

    public function uptime(): int
    {
        if (function_exists('hrtime')) {
            [$secs, $nanos] = hrtime();

            return $secs * 1000 + (int) ($nanos / 1000000);
        }

        return (int) ((microtime(true) - strtotime('Last Monday')) * 1000);
    }
}
