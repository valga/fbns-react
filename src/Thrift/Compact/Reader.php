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
class Reader
{
    /** @var SplStack */
    private $stack;

    /** @var int */
    private $field;

    /** @var ReadBuffer */
    private $buffer;

    public function __construct(string $buffer = '')
    {
        $this->stack = new SplStack();
        $this->buffer = new ReadBuffer($buffer);
    }

    public function __invoke(): Struct
    {
        $this->buffer->rewind();
        $this->field = 0;
        while (!$this->stack->isEmpty()) {
            $this->stack->pop();
        }

        return new Struct($this->readStruct());
    }

    private function readStruct(): \Generator
    {
        while ($this->buffer->isEmpty()) {
            $type = $this->readField();
            switch ($type) {
                case Types::STRUCT:
                    $field = $this->field;
                    $this->stack->push($this->field);
                    $this->field = 0;
                    yield $field => new Struct($this->readStruct());
                    break;
                case Types::STOP:
                    if (!$this->stack->isEmpty()) {
                        $this->field = $this->stack->pop();
                    }

                    return;
                case Types::LIST:
                    $sizeAndType = $this->buffer->readUnsignedByte();
                    $size = $sizeAndType >> 4;
                    $listType = $sizeAndType & 0x0f;
                    if ($size === 0x0f) {
                        $size = $this->buffer->readVarint();
                    }
                    yield $this->field => new Series($listType, $this->readList($size, $listType));
                    break;
                case Types::TRUE:
                case Types::FALSE:
                    yield $this->field => new Field($type, $type === Types::TRUE);
                    break;
                case Types::BYTE:
                    yield $this->field => new Field($type, $this->buffer->readSignedByte());
                    break;
                case Types::I16:
                case Types::I32:
                case Types::I64:
                    yield $this->field => new Field($type, $this->fromZigZag($this->buffer->readVarint()));
                    break;
                case Types::BINARY:
                    yield $this->field => new Field($type, $this->buffer->readString($this->buffer->readVarint()));
                    break;
                case Types::MAP:
                    $size = $this->buffer->readVarint();
                    $types = $this->buffer->readUnsignedByte();
                    $keyType = $types >> 4;
                    $valueType = $types & 0x0f;
                    yield $this->field => new Map($keyType, $valueType, $this->readMap($size, $keyType, $valueType));
                    break;
                default:
                    throw new \DomainException("Unsupported field type {$type}.");
            }
        }
    }

    private function readField(): int
    {
        $typeAndDelta = $this->buffer->readUnsignedByte();
        if ($typeAndDelta === Types::STOP) {
            return Types::STOP;
        }
        $delta = $typeAndDelta >> 4;
        if ($delta === 0) {
            $this->field = $this->fromZigZag($this->buffer->readVarint());
        } else {
            $this->field += $delta;
        }
        $type = $typeAndDelta & 0x0f;

        return $type;
    }

    private function readList(int $size, int $type): \Generator
    {
        for ($i = 0; $i < $size; $i++) {
            yield $this->readPrimitive($type);
        }
    }

    /**
     * @return mixed
     */
    private function readPrimitive(int $type)
    {
        switch ($type) {
            case Types::TRUE:
            case Types::FALSE:
                return $this->buffer->readSignedByte() === Types::TRUE;
            case Types::BYTE:
                return $this->buffer->readSignedByte();
            case Types::I16:
            case Types::I32:
            case Types::I64:
                return $this->fromZigZag($this->buffer->readVarint());
            case Types::BINARY:
                return $this->buffer->readString($this->buffer->readVarint());
            default:
                throw new \DomainException("Unsupported primitive type {$type}.");
        }
    }

    private function readMap(int $size, int $keyType, int $valueType): \Generator
    {
        for ($i = 0; $i < $size; $i++) {
            yield $this->readPrimitive($keyType) => $this->readPrimitive($valueType);
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
        $result = ($n >> 1) ^ -($n & 1);
        if (PHP_INT_SIZE === 4) {
            $result = gmp_strval($result, 10);
        }

        return $result;
    }
}
