<?php

declare(strict_types=1);

namespace Fbns\Lite;

use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\Packet\BasePacket;
use BinSoul\Net\Mqtt\PacketStream;

/**
 * Represents the CONNECT packet.
 */
class ConnectRequestPacket extends BasePacket
{
    /** @var int */
    private $protocolLevel = 3;
    /** @var string */
    private $protocolName = 'MQTToT';
    /** @var int */
    private $flags = 194;
    /** @var int */
    private $keepAlive = 900;
    /** @var string */
    private $payload;

    protected static $packetType = Packet::TYPE_CONNECT;

    public function read(PacketStream $stream): void
    {
        parent::read($stream);
        $this->assertPacketFlags(0);
        $this->assertRemainingPacketLength();

        $originalPosition = $stream->getPosition();
        $this->protocolName = $stream->readString();
        $this->protocolLevel = $stream->readByte();
        $this->flags = $stream->readByte();
        $this->keepAlive = $stream->readWord();

        $payloadLength = $this->remainingPacketLength - ($stream->getPosition() - $originalPosition);
        $this->payload = $stream->read($payloadLength);
    }

    public function write(PacketStream $stream): void
    {
        $data = new PacketStream();

        $data->writeString($this->protocolName);
        $data->writeByte($this->protocolLevel);
        $data->writeByte($this->flags);
        $data->writeWord($this->keepAlive);
        $data->write($this->payload);

        $this->remainingPacketLength = $data->length();

        parent::write($stream);
        $stream->write($data->getData());
    }

    /**
     * Returns the protocol level.
     */
    public function getProtocolLevel(): int
    {
        return $this->protocolLevel;
    }

    /**
     * Sets the protocol level.
     */
    public function setProtocolLevel(int $value): void
    {
        if ($value != 3) {
            throw new \InvalidArgumentException(sprintf('Unknown protocol level %d.', $value));
        }

        $this->protocolLevel = $value;
    }

    /**
     * Returns the payload.
     */
    public function getPayload(): string
    {
        return $this->payload;
    }

    /**
     * Sets the payload.
     */
    public function setPayload(string $value)
    {
        $this->payload = $value;
    }

    /**
     * Returns the flags.
     */
    public function getFlags(): int
    {
        return $this->flags;
    }

    /**
     * Sets the flags.
     */
    public function setFlags(int $value)
    {
        if ($value > 255) {
            throw new \InvalidArgumentException(sprintf('Expected a flags lower than 255 but got %d.', $value));
        }

        $this->flags = $value;
    }

    /**
     * Returns the keep alive time in seconds.
     */
    public function getKeepAlive(): int
    {
        return $this->keepAlive;
    }

    /**
     * Sets the keep alive time in seconds.
     */
    public function setKeepAlive(int $value): void
    {
        if ($value > 65535) {
            throw new \InvalidArgumentException(sprintf('Expected a keep alive time lower than 65535 but got %d.', $value));
        }

        $this->keepAlive = $value;
    }

    /**
     * Returns the protocol name.
     */
    public function getProtocolName(): string
    {
        return $this->protocolName;
    }

    /**
     * Sets the protocol name.
     */
    public function setProtocolName(string $value): void
    {
        $this->assertValidStringLength($value, false);

        $this->protocolName = $value;
    }
}
