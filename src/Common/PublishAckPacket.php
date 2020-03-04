<?php

namespace Fbns\Client\Common;

use BinSoul\Net\Mqtt\Packet\PublishAckPacket as BasePublishAckPacket;

class PublishAckPacket extends BasePublishAckPacket
{
    protected function assertPacketFlags(int $value): void
    {
        // Do nothing because of non-standard flags being used.
    }
}
