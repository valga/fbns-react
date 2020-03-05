<?php

declare(strict_types=1);

namespace Fbns\Mqtt;

class QosLevel
{
    public const FIRE_AND_FORGET = 0;
    public const ACKNOWLEDGED_DELIVERY = 1;
    public const ASSURED_DELIVERY = 2;
}
