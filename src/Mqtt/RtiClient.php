<?php

declare(strict_types=1);

namespace Fbns\Mqtt;

use BinSoul\Net\Mqtt\Client\React\ReactMqttClient;
use BinSoul\Net\Mqtt\DefaultMessage;
use BinSoul\Net\Mqtt\Message;
use BinSoul\Net\Mqtt\StreamParser;
use Evenement\EventEmitterInterface;
use Evenement\EventEmitterTrait;
use Fbns\Connection;
use Fbns\Lite\ConnectResponsePacket;
use Fbns\Lite\FlowFactory;
use Fbns\Lite\PacketFactory;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use React\Promise\Deferred;
use React\Promise\FulfilledPromise;
use React\Promise\PromiseInterface;
use React\Socket\ConnectorInterface;

class RtiClient implements EventEmitterInterface
{
    use EventEmitterTrait;

    private const DEFLATE_LEVEL = 9;

    /** @var ReactMqttClient */
    private $mqttClient;

    /** @var LoggerInterface */
    private $logger;

    /** @var TopicMapper */
    private $topicMapper;

    /** @var LoopInterface */
    private $loop;

    /** @var TimerInterface */
    private $keepaliveTimer;

    /** @var ResettableIdentifierGenerator */
    private $identifierGenerator;

    public function __construct(
        LoopInterface $loop,
        ConnectorInterface $connector,
        LoggerInterface $logger,
        TopicMapper $mapper)
    {
        $this->loop = $loop;
        $this->identifierGenerator = new ResettableIdentifierGenerator();
        $packetFactory = new PacketFactory();
        $this->mqttClient = new ReactMqttClient(
            $connector,
            $loop,
            $this->identifierGenerator,
            new FlowFactory(
                $this->identifierGenerator,
                $this->identifierGenerator,
                $packetFactory
            ),
            new StreamParser($packetFactory)
        );
        $this->logger = $logger;
        $this->topicMapper = $mapper;
        $this->bindEvents($this->mqttClient);
    }

    public function connect(string $host, int $port, Connection $connection, int $timeout): PromiseInterface
    {
        $deferred = new Deferred();
        $this->disconnect()
            ->then(function () use ($deferred, $host, $port, $connection, $timeout) {
                $this->logger->info("Connecting to {$host}:{$port}...");
                $this->identifierGenerator->resetPacketIdentifier();
                $this->mqttClient->connect($host, $port, $connection, $timeout)
                    ->then(function (ConnectResponsePacket $responsePacket) use ($deferred) {
                        $this->emit('connect', [$responsePacket]);
                        $deferred->resolve($responsePacket);
                    })
                    ->otherwise(static function (\Throwable $error) use ($deferred) {
                        $deferred->reject($error);
                    });
            })
            ->otherwise(static function (\Throwable $e) use ($deferred) {
                $deferred->reject($e);
            });

        return $deferred->promise();
    }

    public function publish(string $topic, string $payload, int $qosLevel): PromiseInterface
    {
        $this->logger->debug("Sending message to topic '{$topic}'", [$payload]);
        $mappedTopic = $this->topicMapper->map($topic);
        $deflatedPayload = zlib_encode($payload, ZLIB_ENCODING_DEFLATE, self::DEFLATE_LEVEL);
        $message = new DefaultMessage($mappedTopic, $deflatedPayload, $qosLevel);

        return $this->mqttClient->publish($message);
    }

    public function disconnect(): PromiseInterface
    {
        if (!$this->mqttClient->isConnected()) {
            return new FulfilledPromise($this);
        }

        $deferred = new Deferred();
        $this->mqttClient->disconnect()
            ->then(function () use ($deferred) {
                $deferred->resolve($this);
            })
            ->otherwise(static function (\Throwable $e) use ($deferred) {
                $deferred->reject($e);
            });

        return $deferred->promise();
    }

    public function forceDisconnect(): void
    {
        $this->logger->warning('Forcing disconnect from the broker');
        $closure = static function (ReactMqttClient $client) {
            $client->stream->close();
        };
        ($closure->bindTo(null, $this->mqttClient))($this->mqttClient);
    }

    public function isConnected(): bool
    {
        return $this->mqttClient->isConnected();
    }

    private function bindEvents(EventEmitterInterface $target): void
    {
        $target
            ->on('open', function () {
                $this->logger->debug('Network connection has been established');
            })
            ->on('close', function () {
                $this->logger->debug('Network connection has been closed');
                $this->cancelKeepaliveTimer();
                $this->emit('disconnect', [$this]);
            })
            ->on('warning', function (\Throwable $e) {
                $this->logger->warning($e->getMessage());
            })
            ->on('error', function (\Throwable $e) {
                $this->logger->error($e->getMessage());
                $this->emit('error', [$e]);
            })
            ->on('connect', function () {
                $this->logger->info('Connected to the broker');
                $this->setKeepaliveTimer();
            })
            ->on('disconnect', function () {
                $this->logger->info('Disconnected from the broker');
            })
            ->on('message', function (Message $message) {
                $this->setKeepaliveTimer();
                $this->handleMessage($message);
            })
            ->on('publish', function () {
                $this->logger->debug('Publish flow has been completed');
                $this->setKeepaliveTimer();
            })
            ->on('ping', function () {
                $this->logger->debug('Ping flow has been completed');
                $this->setKeepaliveTimer();
            });
    }

    private function handleMessage(Message $message): void
    {
        $unmappedTopic = $this->topicMapper->unmap($message->getTopic());
        $inflatedPayload = @zlib_decode($message->getPayload());
        if ($inflatedPayload === false) {
            $this->logger->warning("Failed to inflate the payload from topic '{$unmappedTopic}'");

            return;
        }
        $this->logger->debug("Received a message from topic '{$unmappedTopic}'", [$inflatedPayload]);
        $clone = $message->withTopic($unmappedTopic)->withPayload($inflatedPayload);
        $this->emit('message', [$clone]);
    }

    private function cancelKeepaliveTimer(): void
    {
        if ($this->keepaliveTimer === null) {
            return;
        }
        $this->logger->debug('Existing keepalive timer has been canceled');
        $this->loop->cancelTimer($this->keepaliveTimer);
        $this->keepaliveTimer = null;
    }

    private function setKeepaliveTimer(): void
    {
        $this->cancelKeepaliveTimer();
        $keepaliveInterval = RtiConnection::KEEPALIVE_INTERVAL;
        $this->logger->debug("Setting up keepalive timer to {$keepaliveInterval} seconds");
        $this->keepaliveTimer = $this->loop->addTimer($keepaliveInterval, function () {
            $this->logger->info('Keepalive timer has been fired');
            $this->keepaliveTimer = null;
            $this->cancelKeepaliveTimer();
            $this->forceDisconnect();
        });
    }
}
