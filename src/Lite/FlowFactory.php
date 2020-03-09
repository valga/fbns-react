<?php

declare(strict_types=1);

namespace Fbns\Lite;

use BinSoul\Net\Mqtt\ClientIdentifierGenerator;
use BinSoul\Net\Mqtt\Connection;
use BinSoul\Net\Mqtt\Flow;
use BinSoul\Net\Mqtt\Message;
use BinSoul\Net\Mqtt\PacketFactory;
use BinSoul\Net\Mqtt\PacketIdentifierGenerator;
use Fbns\React\FlowFactory as FlowFactoryInterface;

class FlowFactory implements FlowFactoryInterface
{
    /**
     * @var ClientIdentifierGenerator
     */
    private $clientIdentifierGenerator;
    /**
     * @var PacketIdentifierGenerator
     */
    private $packetIdentifierGenerator;
    /**
     * @var PacketFactory
     */
    private $packetFactory;

    public function __construct(
        ClientIdentifierGenerator $clientIdentifierGenerator,
        PacketIdentifierGenerator $packetIdentifierGenerator,
        PacketFactory $packetFactory
    ) {
        $this->clientIdentifierGenerator = $clientIdentifierGenerator;
        $this->packetIdentifierGenerator = $packetIdentifierGenerator;
        $this->packetFactory = $packetFactory;
    }

    public function buildIncomingPingFlow(): Flow
    {
        return new Flow\IncomingPingFlow($this->packetFactory);
    }

    public function buildIncomingPublishFlow(Message $message, int $identifier = null): Flow
    {
        return new Flow\IncomingPublishFlow($this->packetFactory, $message, $identifier);
    }

    public function buildOutgoingConnectFlow(Connection $connection): Flow
    {
        return new OutgoingConnectFlow($this->packetFactory, $connection, $this->clientIdentifierGenerator);
    }

    public function buildOutgoingDisconnectFlow(Connection $connection): Flow
    {
        return new Flow\OutgoingDisconnectFlow($this->packetFactory, $connection);
    }

    public function buildOutgoingPingFlow(): Flow
    {
        return new Flow\OutgoingPingFlow($this->packetFactory);
    }

    public function buildOutgoingPublishFlow(Message $message): Flow
    {
        return new Flow\OutgoingPublishFlow($this->packetFactory, $message, $this->packetIdentifierGenerator);
    }

    public function buildOutgoingSubscribeFlow(array $subscriptions): Flow
    {
        return new Flow\OutgoingSubscribeFlow($this->packetFactory, $subscriptions, $this->packetIdentifierGenerator);
    }

    public function buildOutgoingUnsubscribeFlow(array $subscriptions): Flow
    {
        return new Flow\OutgoingUnsubscribeFlow($this->packetFactory, $subscriptions, $this->packetIdentifierGenerator);
    }
}
