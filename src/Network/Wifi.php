<?php

declare(strict_types=1);

namespace Fbns\Client\Network;

use Fbns\Client\Network;

class Wifi implements Network
{
    public function type(): int
    {
        return NetworkType::WIFI;
    }

    public function subtype(): int
    {
        return NetworkSubtype::UNKNOWN;
    }
}
