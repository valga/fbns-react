<?php

declare(strict_types=1);

namespace Fbns\Proto;

use Fbns\Mqtt\PublishFormat;
use Fbns\Thrift\Compact\Types;
use Fbns\Thrift\Field;
use Fbns\Thrift\Series;
use Fbns\Thrift\Struct;

class ClientInfo implements \JsonSerializable
{
    /** @var int */
    public $userId;
    /** @var string */
    public $userAgent;
    /** @var int */
    public $clientCapabilities;
    /** @var int */
    public $endpointCapabilities;
    /** @var int */
    public $publishFormat;
    /** @var bool */
    public $noAutomaticForeground;
    /** @var bool */
    public $makeUserAvailableInForeground;
    /** @var string */
    public $deviceId;
    /** @var bool */
    public $isInitiallyForeground;
    /** @var int */
    public $networkType;
    /** @var int */
    public $networkSubtype;
    /** @var int */
    public $clientMqttSessionId;
    /** @var string */
    public $clientIpAddress;
    /** @var int[] */
    public $subscribeTopics;
    /** @var string */
    public $clientType;
    /** @var int */
    public $appId;
    /** @var bool */
    public $overrideNectarLogging;
    /** @var string */
    public $connectTokenHash;
    /** @var string */
    public $regionPreference;
    /** @var string */
    public $deviceSecret;
    /** @var int */
    public $clientStack;
    /** @var int */
    public $fbnsConnectionKey;
    /** @var string */
    public $fbnsConnectionSecret;
    /** @var string */
    public $fbnsDeviceId;
    /** @var string */
    public $fbnsDeviceSecret;
    /** @var int */
    public $luid;

    public function __construct(?Struct $struct = null)
    {
        if ($struct !== null) {
            $this->fillFrom($struct);
        }
    }

    private function fillFrom(Struct $struct): void
    {
        /** @var Field $field */
        foreach ($struct->value() as $idx => $field) {
            switch ($idx) {
                case 1:
                    $this->userId = $field->value();
                    break;
                case 2:
                    $this->userAgent = $field->value();
                    break;
                case 3:
                    $this->clientCapabilities = $field->value();
                    break;
                case 4:
                    $this->endpointCapabilities = $field->value();
                    break;
                case 5:
                    $this->publishFormat = $field->value();
                    break;
                case 6:
                    $this->noAutomaticForeground = $field->value();
                    break;
                case 7:
                    $this->makeUserAvailableInForeground = $field->value();
                    break;
                case 8:
                    $this->deviceId = $field->value();
                    break;
                case 9:
                    $this->isInitiallyForeground = $field->value();
                    break;
                case 10:
                    $this->networkType = $field->value();
                    break;
                case 11:
                    $this->networkSubtype = $field->value();
                    break;
                case 12:
                    $this->clientMqttSessionId = $field->value();
                    break;
                case 13:
                    $this->clientIpAddress = $field->value();
                    break;
                case 14:
                    $this->subscribeTopics = [];
                    foreach ($field->value() as $value) {
                        $this->subscribeTopics[] = $value;
                    }
                    break;
                case 15:
                    $this->clientType = $field->value();
                    break;
                case 16:
                    $this->appId = $field->value();
                    break;
                case 17:
                    $this->overrideNectarLogging = $field->value();
                    break;
                case 18:
                    $this->connectTokenHash = $field->value();
                    break;
                case 19:
                    $this->regionPreference = $field->value();
                    break;
                case 20:
                    $this->deviceSecret = $field->value();
                    break;
                case 21:
                    $this->clientStack = $field->value();
                    break;
                case 22:
                    $this->fbnsConnectionKey = $field->value();
                    break;
                case 23:
                    $this->fbnsConnectionSecret = $field->value();
                    break;
                case 24:
                    $this->fbnsDeviceId = $field->value();
                    break;
                case 25:
                    $this->fbnsDeviceSecret = $field->value();
                    break;
                case 26:
                    $this->luid = $field->value();
                    break;
            }
        }
    }

    public function toStruct(): Struct
    {
        return new Struct((function () {
            yield 1 => new Field(Types::I64, $this->userId);
            yield 2 => new Field(Types::BINARY, $this->userAgent);
            yield 3 => new Field(Types::I64, $this->clientCapabilities);
            yield 4 => new Field(Types::I64, $this->endpointCapabilities);
            yield 5 => new Field(Types::I32, $this->publishFormat);
            yield 6 => new Field(Types::TRUE, $this->noAutomaticForeground);
            yield 7 => new Field(Types::TRUE, $this->makeUserAvailableInForeground);
            yield 8 => new Field(Types::BINARY, $this->deviceId);
            yield 9 => new Field(Types::TRUE, $this->isInitiallyForeground);
            yield 10 => new Field(Types::I32, $this->networkType);
            yield 11 => new Field(Types::I32, $this->networkSubtype);
            yield 12 => new Field(Types::I64, $this->clientMqttSessionId);
            yield 13 => new Field(Types::BINARY, $this->clientIpAddress);
            yield 14 => new Series(Types::I32, $this->subscribeTopics);
            yield 15 => new Field(Types::BINARY, $this->clientType);
            yield 16 => new Field(Types::I64, $this->appId);
            yield 17 => new Field(Types::TRUE, $this->overrideNectarLogging);
            yield 18 => new Field(Types::BINARY, $this->connectTokenHash);
            yield 19 => new Field(Types::BINARY, $this->regionPreference);
            yield 20 => new Field(Types::BINARY, $this->deviceSecret);
            // It's not a bug.
            yield 26 => new Field(Types::I64, $this->luid);
            yield 21 => new Field(Types::BYTE, $this->clientStack);
            yield 22 => new Field(Types::I64, $this->fbnsConnectionKey);
            yield 23 => new Field(Types::BINARY, $this->fbnsConnectionSecret);
            yield 24 => new Field(Types::BINARY, $this->fbnsDeviceId);
            yield 25 => new Field(Types::BINARY, $this->fbnsDeviceSecret);
        })());
    }

    private function formatPublishFormat(): ?string
    {
        switch ($this->publishFormat) {
            case PublishFormat::JZ:
                return 'jz';
            case PublishFormat::JZO:
                return 'jzo';
            default:
                return null;
        }
    }

    public function jsonSerialize()
    {
        return [
            // USER_ID
            'u' => $this->userId,
            // SESSION_ID
            //'s' => $this->???,
            // AGENT
            'a' => $this->userAgent,
            // CAPABILITIES
            'cp' => $this->clientCapabilities,
            // ENDPOINT_CAPABILITIES
            'ecp' => $this->endpointCapabilities,
            // PUBLISH_FORMAT
            'pf' => $this->formatPublishFormat(),
            // NO_AUTOMATIC_FOREGROUND
            'no_auto_fg' => $this->noAutomaticForeground,
            // MAKE_USER_AVAILABLE_IN_FOREGROUND
            'chat_on' => $this->makeUserAvailableInForeground,
            // INITIAL_FOREGROUND_STATE
            'fg' => $this->isInitiallyForeground,
            // DEVICE_SECRET
            'ds' => $this->deviceSecret,
            // NETWORK_TYPE
            'nwt' => $this->networkType,
            // NETWORK_SUBTYPE
            'nwst' => $this->networkSubtype,
            // CLIENT_MQTT_SESSION_ID
            'mqtt_sid' => $this->clientMqttSessionId,
            // SUBSCRIBE_TOPICS
            'st' => $this->subscribeTopics,
            // CLIENT_TYPE
            'ct' => $this->clientType,
            // APP_ID
            'aid' => $this->appId,
            // OVERRIDE_NECTAR_LOGGING
            'log' => $this->overrideNectarLogging,
            // DATACENTER_PREFERENCE
            'dc' => $this->regionPreference,
            // CONNECT_HASH
            'h' => $this->connectTokenHash,
            // FBNS_CONNECTION_KEY
            'fbnsck' => $this->fbnsConnectionKey,
            // FBNS_CONNECTION_SECRET
            'fbnscs' => $this->fbnsConnectionSecret,
            // FBNS_DEVICE_ID
            'fbnsdi' => $this->fbnsDeviceId,
            // FBNS_DEVICE_SECRET
            'fbnsds' => $this->fbnsDeviceSecret,
            // CLIENT_STACK
            'clientStack' => $this->clientStack,
            // NETWORK_TYPE_INFO
            //'nwti' => $this->???,
        ];
    }
}
