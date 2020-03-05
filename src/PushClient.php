<?php

declare(strict_types=1);

namespace Fbns;

use BinSoul\Net\Mqtt\Message;
use Evenement\EventEmitterInterface;
use Evenement\EventEmitterTrait;
use Fbns\Endpoint\PushEndpoint;
use Fbns\Lite\ConnectResponsePacket;
use Fbns\Mqtt\FbnsClient;
use Fbns\Mqtt\FbnsConnection;
use Fbns\Mqtt\QosLevel;
use Fbns\Push\Notification;
use Fbns\Push\PushTopics;
use Fbns\Push\Registration;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use React\Socket\Connector;
use React\Socket\ConnectorInterface;
use React\Socket\SecureConnector;

class PushClient implements EventEmitterInterface
{
    use EventEmitterTrait;

    /** @var FbnsConnection */
    private $connection;

    /** @var FbnsClient */
    private $fbnsClient;

    /** @var Deferred[] */
    private $pendingRegistrations;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        LoopInterface $loop,
        Auth $auth,
        Device $device,
        Network $network,
        LoggerInterface $logger = null,
        ConnectorInterface $connector = null
    ) {
        $this->logger = $logger ?? new NullLogger();
        $connector = $connector ?? new SecureConnector(new Connector($loop), $loop);
        $this->connection = new FbnsConnection($auth, $device, new PushEndpoint(), $network);
        $this->fbnsClient = new FbnsClient($loop, $connector, $this->logger, new PushTopics($this->logger));
        $this->pendingRegistrations = [];
        $this->bindEvents($this->fbnsClient);
    }

    public function connect(string $hostname, int $port, int $timeout = 5): PromiseInterface
    {
        return $this->fbnsClient->connect($hostname, $port, $this->connection, $timeout);
    }

    public function isConnected(): bool
    {
        return $this->fbnsClient->isConnected();
    }

    public function disconnect(): PromiseInterface
    {
        return $this->fbnsClient->disconnect();
    }

    public function forceDisconnect(): void
    {
        $this->fbnsClient->forceDisconnect();
    }

    public function register(string $packageName, string $applicationId): PromiseInterface
    {
        $this->logger->info("Trying to register package '{$packageName}'", [$applicationId]);
        $deferred = new Deferred();

        $payload = json_encode([
            'pkg_name' => $packageName,
            'appid' => $applicationId,
        ]);
        $this->fbnsClient->publish('/fbns_reg_req', $payload, QosLevel::ACKNOWLEDGED_DELIVERY)
            ->then(function () use ($packageName, $deferred) {
                $this->pendingRegistrations[$packageName] = $deferred;
            })
            ->otherwise(static function (\Throwable $e) use ($deferred) {
                $deferred->reject($e);
            });

        return $deferred->promise();
    }

    private function bindEvents(EventEmitterInterface $target): void
    {
        $target
            ->on('connect', function (ConnectResponsePacket $responsePacket) {
                $this->emit('connect', [$responsePacket->getAuth()]);
            })
            ->on('error', function (\Throwable $e) {
                $this->emit('error', [$e]);
            })
            ->on('message', function (Message $message) {
                $this->handle($message);
            })
            ->on('disconnect', function () {
                $reason = new \RuntimeException('Disconnected from the broker.');
                foreach ($this->pendingRegistrations as $deferred) {
                    $deferred->reject($reason);
                }
                $this->emit('disconnect', [$this]);
            });
    }

    private function handle(Message $message): void
    {
        $topic = $message->getTopic();
        $payload = $message->getPayload();
        switch ($topic) {
            case '/fbns_reg_resp':
                $this->handleRegistration($payload);
                break;
            case '/fbns_msg':
                $this->handleNotification($payload);
                break;
            default:
                $this->logger->warning("Received a message from unknown topic '{$topic}'");
        }
    }

    private function handleRegistration(string $payload): void
    {
        try {
            $registration = new Registration($payload);
        } catch (\Throwable $e) {
            $this->logger->warning("Failed to decode registration response: {$e->getMessage()}", [$payload]);

            return;
        }

        $packageName = $registration->getPackageName();
        if (!isset($this->pendingRegistrations[$packageName])) {
            $this->logger->warning("Received registration response for unknown package '{$packageName}'", [$payload]);

            return;
        }

        $deferred = $this->pendingRegistrations[$packageName];
        $error = $registration->getError();
        if (!empty($error)) {
            $this->logger->error("Failed to register package '{$packageName}': {$error}", [$payload]);
            $deferred->reject(new \RuntimeException($registration->getError()));

            return;
        }

        $this->logger->info("Package '{$packageName}' has been registered", [$registration->getToken()]);
        $deferred->resolve($registration);
    }

    private function handleNotification(string $payload): void
    {
        try {
            $notification = new Notification($payload);
        } catch (\Throwable $e) {
            $this->logger->warning("Failed to decode push notification: {$e->getMessage()}", [$payload]);

            return;
        }

        $this->emit('push', [$notification]);
    }
}
