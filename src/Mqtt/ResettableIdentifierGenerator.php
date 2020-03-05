<?php

declare(strict_types=1);

namespace Fbns\Mqtt;

use BinSoul\Net\Mqtt\ClientIdentifierGenerator;
use BinSoul\Net\Mqtt\PacketIdentifierGenerator;
use Ramsey\Uuid\Uuid;

class ResettableIdentifierGenerator implements PacketIdentifierGenerator, ClientIdentifierGenerator
{
    /** @var int */
    private $packetIdentifier = 0;

    public function generatePacketIdentifier(): int
    {
        $this->packetIdentifier++;
        if ($this->packetIdentifier > 0xFFFF) {
            $this->packetIdentifier = 1;
        }

        return $this->packetIdentifier;
    }

    public function generateClientIdentifier(): string
    {
        return Uuid::uuid4()->toString();
    }

    public function resetPacketIdentifier(): void
    {
        $this->packetIdentifier = 0;
    }
}
