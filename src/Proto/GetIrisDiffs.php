<?php

declare(strict_types=1);

namespace Fbns\Proto;

use Fbns\Thrift\Compact\Types;
use Fbns\Thrift\Field;
use Fbns\Thrift\Struct;

class GetIrisDiffs
{
    /** @var string */
    public $syncToken;
    /** @var int */
    public $lastSeqId;
    /** @var int */
    public $maxDeltasAbleToProcess;
    /** @var int */
    public $deltaBatchSize;
    /** @var string */
    public $encoding;
    /** @var string */
    public $queueType;
    /** @var int */
    public $syncApiVersion;
    /** @var string */
    public $deviceId;
    /** @var string */
    public $deviceParams;
    /** @var string */
    public $queueParams;
    /** @var int */
    public $entityFbid;
    /** @var int */
    public $syncTokenLong;

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
                    $this->syncToken = $field->value();
                    break;
                case 2:
                    $this->lastSeqId = $field->value();
                    break;
                case 3:
                    $this->maxDeltasAbleToProcess = $field->value();
                    break;
                case 4:
                    $this->deltaBatchSize = $field->value();
                    break;
                case 5:
                    $this->encoding = $field->value();
                    break;
                case 6:
                    $this->queueType = $field->value();
                    break;
                case 7:
                    $this->syncApiVersion = $field->value();
                    break;
                case 8:
                    $this->deviceId = $field->value();
                    break;
                case 9:
                    $this->deviceParams = $field->value();
                    break;
                case 10:
                    $this->queueParams = $field->value();
                    break;
                case 11:
                    $this->entityFbid = $field->value();
                    break;
                case 12:
                    $this->syncTokenLong = $field->value();
                    break;
            }
        }
    }

    public function toStruct(): Struct
    {
        return new Struct((function () {
            yield 1 => new Field(Types::BINARY, $this->syncToken);
            yield 2 => new Field(Types::I64, $this->lastSeqId);
            yield 3 => new Field(Types::I32, $this->maxDeltasAbleToProcess);
            yield 4 => new Field(Types::I32, $this->deltaBatchSize);
            yield 5 => new Field(Types::BINARY, $this->encoding);
            yield 6 => new Field(Types::BINARY, $this->queueType);
            yield 7 => new Field(Types::I32, $this->syncApiVersion);
            yield 8 => new Field(Types::BINARY, $this->deviceId);
            yield 9 => new Field(Types::BINARY, $this->deviceParams);
            yield 10 => new Field(Types::BINARY, $this->queueParams);
            yield 11 => new Field(Types::I64, $this->entityFbid);
            yield 12 => new Field(Types::I64, $this->syncTokenLong);
        })());
    }
}
