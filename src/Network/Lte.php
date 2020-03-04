<?php

declare(strict_types=1);

namespace Fbns\Client\Network;

use Fbns\Client\Network;

class Lte implements Network
{
    public function type(): int
    {
        return NetworkType::MOBILE;
    }

    public function subtype(): int
    {
        return NetworkSubtype::LTE;
    }
}
