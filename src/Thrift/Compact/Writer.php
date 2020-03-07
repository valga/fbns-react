<?php

declare(strict_types=1);

namespace Fbns\Thrift\Compact;

use Fbns\Thrift\Field;
use Fbns\Thrift\Map;
use Fbns\Thrift\Series;
use Fbns\Thrift\Struct;
use Fbns\Thrift\StructSerializable;

/**
 * WARNING: This implementation is not complete.
 *
 * @see https://thrift.apache.org/
 */
class Writer
{
    /** @var WriteBuffer */
    private $buffer;

    public function __construct()
    {
        $this->buffer = new WriteBuffer();
    }

    public function __invoke(StructSerializable $object): string
    {
        $this->buffer->clear();
        $this->writeStruct($object->toStruct());

        return (string) $this->buffer;
    }

    private function writeStruct(Struct $struct): void
    {
        $previousId = 0;

        /** @var Field $field */
        foreach ($struct->value() as $id => $field) {
            $value = $field->value();
            if ($value === null) {
                continue;
            }

            $type = $field->type();
            switch ($type) {
                case Types::TRUE:
                case Types::FALSE:
                    // Boolean fields are inlined, so we have to write them without value.
                    $this->writeField($id, $value ? Types::TRUE : Types::FALSE, $previousId);
                    break;
                default:
                    $this->writeField($id, $type, $previousId);
                    $this->unwrapAndWriteValue($field);
            }

            $previousId = $id;
        }

        $this->buffer->writeByte(Types::STOP);
    }

    private function writeField(int $field, int $type, int $currentField): void
    {
        $delta = $field - $currentField;
        if ((0 < $delta) && ($delta <= 15)) {
            $this->buffer->writeByte(($delta << 4) | $type);
        } else {
            $this->buffer->writeByte($type);
            $this->buffer->writeVarint($this->toZigZag($field, 16));
        }
    }

    private function unwrapAndWriteValue(Field $value): void
    {
        $type = $value->type();
        switch ($type) {
            case Types::STRUCT:
            case Types::LIST:
            case Types::MAP:
                $this->writeValue($type, $value);
                break;
            default:
                $this->writeValue($type, $value->value());
        }
    }

    /**
     * @param mixed $value
     */
    private function writeValue(int $type, $value): void
    {
        switch ($type) {
            case Types::STRUCT:
                /* @var Struct $value */
                $this->writeStruct($value);
                break;
            case Types::LIST:
                /* @var Series $value */
                $this->writeList($value->itemType(), $value->value());
                break;
            case Types::MAP:
                /* @var Map $value */
                $this->writeMap($value->keyType(), $value->valueType(), $value->value());
                break;
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
            case Types::FLOAT:
                $this->buffer->writeFloatingPoint($value, 'G');
                break;
            case Types::DOUBLE:
                $this->buffer->writeFloatingPoint($value, 'E');
                break;
            case Types::BINARY:
                $this->buffer->writeVarint(strlen($value));
                $this->buffer->writeBinary($value);
                break;
            default:
                throw new \DomainException("Unsupported type {$type}.");
        }
    }

    private function writeList(int $type, iterable $list): void
    {
        $size = $this->countIterable($list);
        if ($size < 0x0f) {
            $this->buffer->writeByte(($size << 4) | $type);
        } else {
            $this->buffer->writeByte(0xf0 | $type);
            $this->buffer->writeVarint($size);
        }

        foreach ($list as $value) {
            $this->writeValue($type, $value);
        }
    }

    private function writeMap(int $keyType, int $valueType, iterable $map): void
    {
        $size = $this->countIterable($map);
        $this->buffer->writeVarint($size);
        $this->buffer->writeByte(($keyType << 4) | $valueType);

        foreach ($map as $key => $value) {
            $this->writeValue($keyType, $key);
            $this->writeValue($valueType, $value);
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

    private function countIterable(iterable $collection): int
    {
        switch (true) {
            case is_array($collection):
                return count($collection);
            case $collection instanceof \Countable:
                return $collection->count();
            default:
                $count = 0;
                foreach ($collection as $_) {
                    $count++;
                }

                return $count;
        }
    }
}
