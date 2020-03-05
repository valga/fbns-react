<?php

declare(strict_types=1);

namespace Fbns\Client\Lite;

use BinSoul\Net\Mqtt\ClientIdentifierGenerator;
use BinSoul\Net\Mqtt\Connection;
use BinSoul\Net\Mqtt\Flow\OutgoingConnectFlow as BaseOutgoingConnectFlow;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\PacketFactory;

class OutgoingConnectFlow extends BaseOutgoingConnectFlow
{
    const PROTOCOL_LEVEL = 3;

    const PROTOCOL_NAME = 'MQTToT';

    const KEEPALIVE = 900;
    const KEEPALIVE_TIMEOUT = 60;

    /** @var Connection */
    private $connection;

    /**
     * Constructs an instance of this class.
     */
    public function __construct(PacketFactory $packetFactory, Connection $connection, ClientIdentifierGenerator $generator)
    {
        $this->connection = $connection;
        parent::__construct($packetFactory, $connection, $generator);
    }

    public function start(): ?Packet
    {
        $packet = new ConnectRequestPacket();
        $packet->setProtocolLevel(self::PROTOCOL_LEVEL);
        $packet->setProtocolName(self::PROTOCOL_NAME);
        $packet->setKeepAlive(self::KEEPALIVE);
        $packet->setFlags(194);
        $packet->setPayload(zlib_encode($this->connection->toThrift(), ZLIB_ENCODING_DEFLATE, 9));

        return $packet;
    }

    public function next(Packet $packet): ?Packet
    {
        /** @var ConnectResponsePacket $packet */
        if ($packet->isSuccess()) {
            $this->succeed($packet);
        } else {
            $this->fail($packet->getErrorName());
        }

        return null;
    }
}
