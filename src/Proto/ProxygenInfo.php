<?php

declare(strict_types=1);

namespace Fbns\Client\Proto;

use Fbns\Client\Thrift\Compact\Types;
use Fbns\Client\Thrift\Field;
use Fbns\Client\Thrift\Struct;

class ProxygenInfo
{
    /** @var string */
    public $ipAddr;
    /** @var string */
    public $hostName;
    /** @var string */
    public $vipAddr;

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
                    $this->ipAddr = $field->value();
                    break;
                case 2:
                    $this->hostName = $field->value();
                    break;
                case 3:
                    $this->vipAddr = $field->value();
                    break;
            }
        }
    }

    public function toStruct(): Struct
    {
        return new Struct((function () {
            yield 1 => new Field(Types::BINARY, $this->ipAddr);
            yield 2 => new Field(Types::BINARY, $this->hostName);
            yield 3 => new Field(Types::BINARY, $this->vipAddr);
        })());
    }
}
