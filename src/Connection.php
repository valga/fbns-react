<?php

namespace Fbns\Client;

use BinSoul\Net\Mqtt\Connection as ConnectionInterface;
use BinSoul\Net\Mqtt\Message;
use Fbns\Client\Mqtt\ClientCapabilities;
use Fbns\Client\Mqtt\PublishFormat;
use Fbns\Client\Network\Wifi;
use Fbns\Client\Proto\ClientInfo;
use Fbns\Client\Proto\Connect;
use Fbns\Client\Thrift\Compact\Writer;

class Connection implements ConnectionInterface
{
    const FBNS_ENDPOINT_CAPABILITIES = 128;
    const FBNS_APP_ID = '567310203415052';
    const FBNS_CLIENT_STACK = 3;

    /** @var AuthInterface */
    private $auth;

    /** @var string */
    private $userAgent;

    /** @var Network */
    private $network;

    /** @var int */
    private $clientCapabilities;
    /** @var int */
    private $endpointCapabilities;
    /** @var bool */
    private $noAutomaticForeground;
    /** @var bool */
    private $makeUserAvailableInForeground;
    /** @var bool */
    private $isInitiallyForeground;
    /** @var int */
    private $clientMqttSessionId;
    /** @var int[] */
    private $subscribeTopics;
    /** @var int */
    private $appId;
    /** @var int */
    private $clientStack;

    /**
     * Connection constructor.
     *
     * @param string $userAgent
     */
    public function __construct(AuthInterface $auth, $userAgent, Network $network = null)
    {
        $this->auth = $auth;
        $this->userAgent = $userAgent;
        $this->network = $network ?? new Wifi();

        $this->clientCapabilities = ClientCapabilities::DEFAULT_SET;
        $this->endpointCapabilities = self::FBNS_ENDPOINT_CAPABILITIES;
        $this->noAutomaticForeground = true;
        $this->makeUserAvailableInForeground = false;
        $this->isInitiallyForeground = false;
        $this->subscribeTopics = [(int) Lite::MESSAGE_TOPIC_ID, (int) Lite::REG_RESP_TOPIC_ID];
        $this->appId = self::FBNS_APP_ID;
        $this->clientStack = self::FBNS_CLIENT_STACK;
    }

    private function buildClientInfo(): ClientInfo
    {
        $clientInfo = new ClientInfo();
        $clientInfo->userId = $this->auth->getUserId();
        $clientInfo->userAgent = $this->userAgent;
        $clientInfo->clientCapabilities = $this->clientCapabilities;
        $clientInfo->endpointCapabilities = $this->endpointCapabilities;
        $clientInfo->publishFormat = PublishFormat::JZ;
        $clientInfo->noAutomaticForeground = $this->noAutomaticForeground;
        $clientInfo->makeUserAvailableInForeground = $this->makeUserAvailableInForeground;
        $clientInfo->isInitiallyForeground = $this->isInitiallyForeground;
        $clientInfo->networkType = $this->network->type();
        $clientInfo->networkSubtype = $this->network->subtype();
        if ($this->clientMqttSessionId === null) {
            $sessionId = (int) ((microtime(true) - strtotime('Last Monday')) * 1000);
        } else {
            $sessionId = $this->clientMqttSessionId;
        }
        $clientInfo->clientMqttSessionId = $sessionId;
        $clientInfo->subscribeTopics = [(int) Lite::MESSAGE_TOPIC_ID, (int) Lite::REG_RESP_TOPIC_ID];
        $clientInfo->clientType = $this->auth->getClientType();
        $clientInfo->appId = $this->appId;
        $clientInfo->deviceSecret = $this->auth->getDeviceSecret();
        $clientInfo->clientStack = $this->clientStack;

        return $clientInfo;
    }

    private function buildConnect(): Connect
    {
        $connect = new Connect();
        $connect->clientIdentifier = $this->auth->getClientId();
        $connect->clientInfo = $this->buildClientInfo();
        $connect->password = $this->auth->getPassword();

        return $connect;
    }

    public function toThrift(): string
    {
        $connect = $this->buildConnect();
        $writer = new Writer();

        return $writer($connect->toStruct());
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    /**
     * @return int
     */
    public function getClientCapabilities()
    {
        return $this->clientCapabilities;
    }

    /**
     * @param int $clientCapabilities
     */
    public function setClientCapabilities($clientCapabilities)
    {
        $this->clientCapabilities = $clientCapabilities;
    }

    /**
     * @return int
     */
    public function getEndpointCapabilities()
    {
        return $this->endpointCapabilities;
    }

    /**
     * @param int $endpointCapabilities
     */
    public function setEndpointCapabilities($endpointCapabilities)
    {
        $this->endpointCapabilities = $endpointCapabilities;
    }

    /**
     * @return bool
     */
    public function isNoAutomaticForeground()
    {
        return $this->noAutomaticForeground;
    }

    /**
     * @param bool $noAutomaticForeground
     */
    public function setNoAutomaticForeground($noAutomaticForeground)
    {
        $this->noAutomaticForeground = $noAutomaticForeground;
    }

    /**
     * @return bool
     */
    public function isMakeUserAvailableInForeground()
    {
        return $this->makeUserAvailableInForeground;
    }

    /**
     * @param bool $makeUserAvailableInForeground
     */
    public function setMakeUserAvailableInForeground($makeUserAvailableInForeground)
    {
        $this->makeUserAvailableInForeground = $makeUserAvailableInForeground;
    }

    /**
     * @return bool
     */
    public function isInitiallyForeground()
    {
        return $this->isInitiallyForeground;
    }

    /**
     * @param bool $isInitiallyForeground
     */
    public function setIsInitiallyForeground($isInitiallyForeground)
    {
        $this->isInitiallyForeground = $isInitiallyForeground;
    }

    /**
     * @return int
     */
    public function getClientMqttSessionId()
    {
        return $this->clientMqttSessionId;
    }

    /**
     * @param int $clientMqttSessionId
     */
    public function setClientMqttSessionId($clientMqttSessionId)
    {
        $this->clientMqttSessionId = $clientMqttSessionId;
    }

    /**
     * @return int[]
     */
    public function getSubscribeTopics()
    {
        return $this->subscribeTopics;
    }

    /**
     * @param int[] $subscribeTopics
     */
    public function setSubscribeTopics($subscribeTopics)
    {
        $this->subscribeTopics = $subscribeTopics;
    }

    /**
     * @return int
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @param int $appId
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
    }

    /**
     * @return int
     */
    public function getClientStack()
    {
        return $this->clientStack;
    }

    /**
     * @param int $clientStack
     */
    public function setClientStack($clientStack)
    {
        $this->clientStack = $clientStack;
    }

    /**
     * @return AuthInterface
     */
    public function getAuth()
    {
        return $this->auth;
    }

    public function setAuth(AuthInterface $auth)
    {
        $this->auth = $auth;
    }

    public function getProtocol(): int
    {
        return 3;
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
        return 100;
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
