<?php

declare(strict_types=1);

namespace Fbns\Thrift\Compact;

use Fbns\Thrift\Field;
use Fbns\Thrift\Map;
use Fbns\Thrift\Series;
use Fbns\Thrift\Set;
use Fbns\Thrift\Struct;

/**
 * @see https://thrift.apache.org/
 */
class Reader
{
    /** @var ReadBuffer */
    private $buffer;

    public function __construct(string $buffer)
    {
        $this->buffer = new ReadBuffer($buffer);
    }

    public function __invoke(): Struct
    {
        $this->buffer->rewind();

        return $this->readStruct();
    }

    private function readStruct(): Struct
    {
        return new Struct($this->readStructFields());
    }

    private function readStructFields(): \Generator
    {
        $field = 0;
        while (!$this->buffer->isEmpty()) {
            [$field, $type] = $this->readNextField($field);
            switch ($type) {
                case Types::STOP:
                    return;
                case Types::TRUE:
                case Types::FALSE:
                    // Boolean fields are inlined, so there is no need to read them.
                    yield $field => new Field(Types::TRUE, $type === Types::TRUE);
                    break;
                default:
                    yield $field => $this->readWrappedValue($type);
            }
        }
    }

    private function readNextField(int $currentField): array
    {
        $typeAndDelta = $this->buffer->readUnsignedByte();
        if ($typeAndDelta === Types::STOP) {
            return [$currentField, Types::STOP];
        }
        $delta = $typeAndDelta >> 4;
        $nextField = $currentField;
        if ($delta === 0) {
            $nextField = $this->fromZigZag($this->buffer->readVarint());
        } else {
            $nextField += $delta;
        }
        $type = $typeAndDelta & 0x0f;

        return [$nextField, $type];
    }

    private function readCollectionHeader(): array
    {
        $sizeAndType = $this->buffer->readUnsignedByte();
        $size = $sizeAndType >> 4;
        $itemType = $sizeAndType & 0x0f;
        if ($size === 0x0f) {
            $size = $this->buffer->readVarint();
        }

        return [$size, $itemType];
    }

    private function readList(): Series
    {
        [$size, $itemType] = $this->readCollectionHeader();

        return new Series($itemType, $this->readCollectionItems($size, $itemType));
    }

    private function readSet(): Set
    {
        [$size, $itemType] = $this->readCollectionHeader();

        return new Set($itemType, $this->readCollectionItems($size, $itemType));
    }

    private function readCollectionItems(int $size, int $type): \Generator
    {
        for ($i = 0; $i < $size; $i++) {
            yield $this->readValue($type);
        }
    }

    private function readMap(): Map
    {
        $size = $this->buffer->readVarint();
        $types = $this->buffer->readUnsignedByte();
        $keyType = $types >> 4;
        $valueType = $types & 0x0f;

        return new Map($keyType, $valueType, $this->readMapItems($size, $keyType, $valueType));
    }

    private function readMapItems(int $size, int $keyType, int $valueType): \Generator
    {
        for ($i = 0; $i < $size; $i++) {
            yield $this->readValue($keyType) => $this->readValue($valueType);
        }
    }

    private function readWrappedValue(int $type): Field
    {
        $result = $this->readValue($type);
        if ($result instanceof Field) {
            return $result;
        }

        return new Field($type, $result);
    }

    /**
     * @return mixed
     */
    private function readValue(int $type)
    {
        switch ($type) {
            case Types::STRUCT:
                return $this->readStruct();
            case Types::LIST:
                return $this->readList();
            case Types::SET:
                return $this->readSet();
            case Types::MAP:
                return $this->readMap();
            case Types::TRUE:
            case Types::FALSE:
                return $this->buffer->readSignedByte() === Types::TRUE;
            case Types::BYTE:
                return $this->buffer->readSignedByte();
            case Types::I16:
            case Types::I32:
            case Types::I64:
                return $this->fromZigZag($this->buffer->readVarint());
            case Types::FLOAT:
                return $this->buffer->readFloatingPoint('G', 4);
            case Types::DOUBLE:
                return $this->buffer->readFloatingPoint('E', 8);
            case Types::BINARY:
                return $this->buffer->readString($this->buffer->readVarint());
            default:
                throw new \DomainException("Unsupported type {$type}.");
        }
    }

    /**
     * @param int|string $n
     *
     * @return int|string
     */
    private function fromZigZag($n)
    {
        if (PHP_INT_SIZE === 4) {
            $n = gmp_init($n, 10);
        }
        if (PHP_INT_SIZE === 8 && $n < 0) {
            $n &= 0x7FFFFFFFFFFFFFFF;
            $result = (($n >> 1) | (1 << 62)) ^ -($n & 1);
            if ($n & 1) {
                $result |= 1 << 63;
            }
        } else {
            $result = ($n >> 1) ^ -($n & 1);
        }
        if (PHP_INT_SIZE === 4) {
            $result = gmp_strval($result, 10);
        }

        return $result;
    }
}
