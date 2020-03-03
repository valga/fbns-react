<?php

declare(strict_types=1);

namespace Fbns\Client\Thrift\Compact;

use Fbns\Client\Thrift\Field;
use Fbns\Client\Thrift\Map;
use Fbns\Client\Thrift\Series;
use Fbns\Client\Thrift\Struct;
use SplStack;

/**
 * WARNING: This implementation is not complete.
 *
 * @see https://thrift.apache.org/
 */
class Writer
{
    /** @var WriteBuffer */
    private $buffer;

    /** @var int */
    private $field;

    /** @var SplStack */
    private $stack;

    public function __construct()
    {
        $this->buffer = new WriteBuffer();
        $this->stack = new SplStack();
    }

    public function __invoke(Struct $struct): string
    {
        $this->buffer->clear();
        while (!$this->stack->isEmpty()) {
            $this->stack->pop();
        }
        $this->field = 0;
        $this->writeStruct($struct);

        return (string) $this->buffer;
    }

    private function writeStruct(Struct $struct): void
    {
        /** @var Field $field */
        foreach ($struct->value() as $idx => $field) {
            $value = $field->value();
            if ($value === null) {
                continue;
            }
            $type = $field->type();
            switch (true) {
                case $field instanceof Struct:
                    $this->writeField($idx, $type);
                    $this->stack[] = $this->field;
                    $this->field = 0;
                    $this->writeStruct($field);
                    break;
                case $field instanceof Series:
                    $this->writeField($idx, $type);
                    $this->writeList($field->itemType(), $field->value());
                    break;
                case $field instanceof Map:
                    $this->writeField($idx, $type);
                    $this->writeMap($field->keyType(), $field->valueType(), $field->value());
                    break;
                case $type === Types::TRUE || $type === Types::FALSE:
                    $this->writeField($idx, $value ? Types::TRUE : Types::FALSE);
                    break;
                default:
                    $this->writeField($idx, $type);
                    $this->writePrimitive($field->type(), $value);
            }
        }

        $this->buffer->writeByte(Types::STOP);
        if (!$this->stack->isEmpty()) {
            $this->field = $this->stack->pop();
        }
    }

    private function writeField(int $field, int $type): void
    {
        $delta = $field - $this->field;
        if ((0 < $delta) && ($delta <= 15)) {
            $this->buffer->writeByte(($delta << 4) | $type);
        } else {
            $this->buffer->writeByte($type);
            $this->buffer->writeVarint($this->toZigZag($field, 16));
        }
        $this->field = $field;
    }

    /**
     * @param mixed $value
     */
    private function writePrimitive(int $type, $value): void
    {
        switch ($type) {
            case Types::TRUE:
            case Types::FALSE:
                $this->buffer->writeByte($value ? Types::TRUE : Types::FALSE);
                break;
            case Types::BYTE:
                $this->buffer->writeByte($value);
                break;
            case Types::I16:
                $this->buffer->writeVarint($this->toZigZag($value, 16));
                break;
            case Types::I32:
                $this->buffer->writeVarint($this->toZigZag($value, 32));
                break;
            case Types::I64:
                $this->buffer->writeVarint($this->toZigZag($value, 64));
                break;
            case Types::BINARY:
                $this->buffer->writeVarint(strlen($value));
                $this->buffer->writeBinary($value);
                break;
            default:
                throw new \DomainException("Unsupported type {$type}.");
        }
    }

    private function writeList(int $type, array $list): void
    {
        $size = count($list);
        if ($size < 0x0f) {
            $this->buffer->writeByte(($size << 4) | $type);
        } else {
            $this->buffer->writeByte(0xf0 | $type);
            $this->buffer->writeVarint($size);
        }

        foreach ($list as $value) {
            $this->writePrimitive($type, $value);
        }
    }

    private function writeMap(int $keyType, int $valueType, array $map): void
    {
        $this->buffer->writeVarint(count($map));
        $this->buffer->writeByte(($keyType << 4) | $valueType);
        foreach ($map as $key => $value) {
            $this->writePrimitive($keyType, $key);
            $this->writePrimitive($valueType, $value);
        }
    }

    /**
     * @param int|string $number
     *
     * @return int|string
     */
    private function toZigZag($number, int $bits)
    {
        if (PHP_INT_SIZE === 4) {
            $number = gmp_init($number, 10);
        }
        $result = ($number << 1) ^ ($number >> ($bits - 1));
        if (PHP_INT_SIZE === 4) {
            $result = gmp_strval($result, 10);
        }

        return $result;
    }
}
