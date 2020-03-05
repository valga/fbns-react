<?php

declare(strict_types=1);

namespace Fbns\Proto;

use Fbns\Thrift\Compact\Types;
use Fbns\Thrift\Field;
use Fbns\Thrift\Map;
use Fbns\Thrift\Series;
use Fbns\Thrift\Struct;
use Fbns\Thrift\StructSerializable;

class Connect implements StructSerializable, \JsonSerializable
{
    /** @var string */
    public $clientIdentifier;
    /** @var string */
    public $willTopic;
    /** @var string */
    public $willMessage;
    /** @var ClientInfo */
    public $clientInfo;
    /** @var string */
    public $password;
    /** @var string[] */
    public $getDiffsRequests;
    /** @var ProxygenInfo[] */
    public $proxygenInfo;
    /** @var CombinedPublish[] */
    public $combinedPublishes;
    /** @var string */
    public $zeroRatingTokenHash;
    /** @var array */
    public $appSpecificInfo;

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
                    $this->clientIdentifier = $field->value();
                    break;
                case 2:
                    $this->willTopic = $field->value();
                    break;
                case 3:
                    $this->willMessage = $field->value();
                    break;
                case 4:
                    $this->clientInfo = new ClientInfo($field);
                    break;
                case 5:
                    $this->password = $field->value();
                    break;
                case 6:
                    $this->getDiffsRequests = [];
                    foreach ($field->value() as $diffRequest) {
                        $this->getDiffsRequests[] = new GetIrisDiffs($diffRequest);
                    }
                    break;
                case 7:
                    $this->proxygenInfo = [];
                    foreach ($field->value() as $proxygenInfo) {
                        $this->proxygenInfo[] = new ProxygenInfo($proxygenInfo);
                    }
                    break;
                case 8:
                    $this->combinedPublishes = [];
                    foreach ($field->value() as $combinedPublish) {
                        $this->combinedPublishes[] = new CombinedPublish($combinedPublish);
                    }
                    break;
                case 9:
                    $this->zeroRatingTokenHash = $field->value();
                    break;
                case 10:
                    $this->appSpecificInfo = [];
                    foreach ($field->value() as $key => $value) {
                        $this->appSpecificInfo[$key] = $value;
                    }
                    break;
            }
        }
    }

    public function toStruct(): Struct
    {
        return new Struct((function () {
            yield 1 => new Field(Types::BINARY, $this->clientIdentifier);
            yield 2 => new Field(Types::BINARY, $this->willTopic);
            yield 3 => new Field(Types::BINARY, $this->willMessage);
            if ($this->clientInfo !== null) {
                yield 4 => $this->clientInfo->toStruct();
            }
            yield 5 => new Field(Types::BINARY, $this->password);
            yield 6 => new Series(Types::BINARY, $this->getDiffsRequests);
            yield 7 => new Series(Types::STRUCT, $this->proxygenInfo);
            yield 8 => new Series(Types::STRUCT, $this->combinedPublishes);
            yield 9 => new Field(Types::BINARY, $this->zeroRatingTokenHash);
            yield 10 => new Map(Types::BINARY, Types::BINARY, $this->appSpecificInfo);
        })());
    }

    public function jsonSerialize()
    {
        $result = [
            // DEVICE_ID
            'd' => $this->clientIdentifier,
            // APP_SPECIFIC_INFO
            'app_specific_info' => $this->appSpecificInfo,
        ];
        if ($this->clientInfo !== null) {
            $result = array_merge($result, $this->clientInfo->jsonSerialize());
        }

        return array_filter($result, static function ($value) {
            return $value !== null;
        });
    }
}
