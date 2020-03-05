<?php

declare(strict_types=1);

namespace Fbns\Network;

use Fbns\Network;

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
