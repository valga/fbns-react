<?php

declare(strict_types=1);

namespace Fbns\Mqtt;

use BinSoul\Net\Mqtt\Message;
use Fbns\Auth;
use Fbns\Connection;
use Fbns\Device;
use Fbns\Endpoint;
use Fbns\Network;
use Fbns\Proto\ClientInfo;
use Fbns\Proto\Connect;
use Fbns\Thrift\Compact\Writer;

class RtiConnection implements Connection
{
    private const CLIENT_STACK = 3;
    public const KEEPALIVE_INTERVAL = 900;

    /** @var Auth */
    private $auth;

    /** @var Device */
    private $device;

    /** @var Endpoint */
    private $endpoint;

    /** @var Network */
    private $network;

    public function __construct(Auth $auth, Device $device, Endpoint $endpoint, Network $network)
    {
        $this->auth = $auth;
        $this->device = $device;
        $this->endpoint = $endpoint;
        $this->network = $network;
    }

    private function buildClientInfo(): ClientInfo
    {
        $clientInfo = new ClientInfo();
        $clientInfo->userId = $this->auth->getUserId();
        $clientInfo->userAgent = $this->device->userAgent();
        $clientInfo->clientCapabilities = ClientCapabilities::DEFAULT_SET;
        $clientInfo->endpointCapabilities = $this->endpoint->capabilities();
        $clientInfo->publishFormat = PublishFormat::JZ;
        $clientInfo->noAutomaticForeground = $this->endpoint->noAutomaticForeground();
        $clientInfo->makeUserAvailableInForeground = $this->endpoint->makeUserAvailableInForeground();
        $clientInfo->deviceId = $this->auth->getDeviceId();
        $clientInfo->isInitiallyForeground = $this->endpoint->isInitiallyForeground();
        $clientInfo->networkType = $this->network->type();
        $clientInfo->networkSubtype = $this->network->subtype();
        $clientInfo->clientMqttSessionId = $this->device->uptime();
        $clientInfo->subscribeTopics = $this->endpoint->subscribeTopics();
        $clientInfo->clientType = $this->auth->getClientType();
        $clientInfo->appId = $this->endpoint->appId();
        $clientInfo->regionPreference = $this->endpoint->regionPreference();
        $clientInfo->deviceSecret = $this->auth->getDeviceSecret();
        $clientInfo->clientStack = self::CLIENT_STACK;
        $clientInfo->luid = $this->endpoint->loggerUserId();

        return $clientInfo;
    }

    private function buildConnect(): Connect
    {
        $connect = new Connect();
        $connect->clientIdentifier = $this->auth->getClientId();
        $connect->clientInfo = $this->buildClientInfo();
        $connect->password = $this->auth->getPassword();
        $connect->appSpecificInfo = $this->endpoint->appSpecificInfo();

        return $connect;
    }

    public function toThrift(): string
    {
        $connect = $this->buildConnect();
        $writer = new Writer();

        return $writer($connect->toStruct());
    }

    public function getProtocol(): int
    {
        return self::CLIENT_STACK;
    }

    public function getClientID(): string
    {
        return $this->auth->getClientId();
    }

    public function isCleanSession(): bool
    {
        return true;
    }

    public function getUsername(): string
    {
        return json_encode($this->buildConnect());
    }

    public function getPassword(): string
    {
        return $this->auth->getPassword();
    }

    public function getWill(): ?Message
    {
        return null;
    }

    public function getKeepAlive(): int
    {
        return self::KEEPALIVE_INTERVAL;
    }

    public function withProtocol(int $protocol): Connection
    {
        throw new \LogicException('Protocol version can not be changed.');
    }

    public function withClientID(string $clientID): Connection
    {
        throw new \LogicException('Client ID must be changed via Auth.');
    }

    public function withCredentials(string $username, string $password): Connection
    {
        throw new \LogicException('Credentials must be changed via Auth.');
    }

    public function withWill(Message $will = null): Connection
    {
        throw new \LogicException('Will is not supported.');
    }

    public function withKeepAlive(int $timeout): Connection
    {
        throw new \LogicException('Keep alive interval can not be changed.');
    }
}
