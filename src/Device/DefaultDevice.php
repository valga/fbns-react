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

    public function uptime()
    {
        if (function_exists('hrtime')) {
            [$secs, $nanos] = hrtime();
            $millies = (int) ($nanos / 1000000);
            if (PHP_INT_SIZE === 4) {
                return sprintf('%d%03d', $secs, $millies);
            }

            return $secs * 1000 + $millies;
        }
        $timeSinceLastMonday = (int) ((microtime(true) - strtotime('Last Monday')) * 1000);

        return PHP_INT_SIZE === 4 ? (string) $timeSinceLastMonday : $timeSinceLastMonday;
    }
}
