<?php

declare(strict_types=1);

namespace Fbns\Network;

use Fbns\Network;

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
