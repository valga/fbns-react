<?php

declare(strict_types=1);

namespace Fbns\Proto;

use Fbns\Thrift\Compact\Types;
use Fbns\Thrift\Field;
use Fbns\Thrift\Struct;
use Fbns\Thrift\StructSerializable;

class CombinedPublish implements StructSerializable
{
    /** @var string */
    public $topic;
    /** @var int */
    public $messageId;
    /** @var string */
    public $payload;

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
                    $this->topic = $field->value();
                    break;
                case 2:
                    $this->messageId = $field->value();
                    break;
                case 3:
                    $this->payload = $field->value();
                    break;
            }
        }
    }

    public function toStruct(): Struct
    {
        return new Struct((function () {
            yield 1 => new Field(Types::BINARY, $this->topic);
            yield 2 => new Field(Types::I32, $this->messageId);
            yield 3 => new Field(Types::BINARY, $this->payload);
        })());
    }
}
