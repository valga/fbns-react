<?php

declare(strict_types=1);

namespace Fbns\Client\Lite;

use BinSoul\Net\Mqtt\Exception\UnknownPacketTypeException;
use BinSoul\Net\Mqtt\Packet;
use BinSoul\Net\Mqtt\PacketFactory as PacketFactoryInterface;
use Fbns\Client\Common\PublishAckPacket;

class PacketFactory implements PacketFactoryInterface
{
    private static $mapping = [
        Packet::TYPE_CONNECT => ConnectRequestPacket::class,
        Packet::TYPE_CONNACK => ConnectResponsePacket::class,
        Packet::TYPE_PUBLISH => Packet\PublishRequestPacket::class,
        Packet::TYPE_PUBACK => PublishAckPacket::class,
        Packet::TYPE_PUBREC => Packet\PublishReceivedPacket::class,
        Packet::TYPE_PUBREL => Packet\PublishReleasePacket::class,
        Packet::TYPE_PUBCOMP => Packet\PublishCompletePacket::class,
        Packet::TYPE_SUBSCRIBE => Packet\SubscribeRequestPacket::class,
        Packet::TYPE_SUBACK => Packet\SubscribeResponsePacket::class,
        Packet::TYPE_UNSUBSCRIBE => Packet\UnsubscribeRequestPacket::class,
        Packet::TYPE_UNSUBACK => Packet\UnsubscribeResponsePacket::class,
        Packet::TYPE_PINGREQ => Packet\PingRequestPacket::class,
        Packet::TYPE_PINGRESP => Packet\PingResponsePacket::class,
        Packet::TYPE_DISCONNECT => Packet\DisconnectRequestPacket::class,
    ];

    public function build(int $type): Packet
    {
        if (!isset(self::$mapping[$type])) {
            throw new UnknownPacketTypeException(sprintf('Unknown packet type %d.', $type));
        }

        $class = self::$mapping[$type];

        return new $class();
    }
}
