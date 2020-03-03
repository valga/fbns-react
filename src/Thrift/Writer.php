<?php

declare(strict_types=1);

namespace Fbns\Client\Thrift;

/**
 * WARNING: This implementation is not complete.
 *
 * @see https://thrift.apache.org/
 */
class Writer
{
    /**
     * @var string
     */
    private $buffer;

    /**
     * @var int
     */
    private $field;

    /**
     * @var int[]
     */
    private $stack;

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

    private function writeByte(int $number): void
    {
        $this->buffer .= chr($number);
    }

    /**
     * @param int|string $number
     */
    private function writeWord($number): void
    {
        $this->writeVarint($this->toZigZag($number, 16));
    }

    /**
     * @param int|string $number
     */
    private function writeInt($number): void
    {
        $this->writeVarint($this->toZigZag($number, 32));
    }

    /**
     * @param int|string $number
     */
    private function writeLongInt($number): void
    {
        $this->writeVarint($this->toZigZag($number, 64));
    }

    private function writeField(int $field, int $type): void
    {
        $delta = $field - $this->field;
        if ((0 < $delta) && ($delta <= 15)) {
            $this->writeByte(($delta << 4) | $type);
        } else {
            $this->writeByte($type);
            $this->writeWord($field);
        }
        $this->field = $field;
    }

    /**
     * @param int|string $number
     */
    private function writeVarint($number): void
    {
        if (PHP_INT_SIZE === 4) {
            $number = gmp_init($number, 10);
        }
        while (true) {
            $byte = $number & (~0x7f);
            if (PHP_INT_SIZE === 4) {
                $byte = (int) gmp_strval($byte, 10);
            }
            if ($byte === 0) {
                if (PHP_INT_SIZE === 4) {
                    $number = (int) gmp_strval($number, 10);
                }
                $this->buffer .= chr($number);
                break;
            }
            $byte = ($number & 0xff) | 0x80;
            if (PHP_INT_SIZE === 4) {
                $byte = (int) gmp_strval($byte, 10);
            }
            $this->buffer .= chr($byte);
            $number >>= 7;
        }
    }

    private function writeBinary(string $data): void
    {
        $this->buffer .= $data;
    }

    /**
     * @param mixed $value
     */
    private function writePrimitive(int $type, $value): void
    {
        switch ($type) {
            case Compact::TYPE_TRUE:
            case Compact::TYPE_FALSE:
                $this->writeByte($value ? Compact::TYPE_TRUE : Compact::TYPE_FALSE);
                break;
            case Compact::TYPE_BYTE:
                $this->writeByte($value);
                break;
            case Compact::TYPE_I16:
                $this->writeWord($value);
                break;
            case Compact::TYPE_I32:
                $this->writeInt($value);
                break;
            case Compact::TYPE_I64:
                $this->writeLongInt($value);
                break;
            case Compact::TYPE_BINARY:
                $this->writeVarint(strlen($value));
                $this->writeBinary($value);
                break;
            default:
                throw new \DomainException("Unsupported primitive type {$type}.");
        }
    }

    public function writeBool(int $field, bool $value): void
    {
        $this->writeField($field, $value ? Compact::TYPE_TRUE : Compact::TYPE_FALSE);
    }

    public function writeString(int $field, string $string): void
    {
        $this->writeField($field, Compact::TYPE_BINARY);
        $this->writeVarint(strlen($string));
        $this->writeBinary($string);
    }

    public function writeStop(): void
    {
        $this->buffer .= chr(Compact::TYPE_STOP);
        if (count($this->stack)) {
            $this->field = array_pop($this->stack);
        }
    }

    /**
     * @param int|string $number
     */
    public function writeInt8(int $field, $number): void
    {
        $this->writeField($field, Compact::TYPE_BYTE);
        $this->writeByte($number);
    }

    /**
     * @param int|string $number
     */
    public function writeInt16(int $field, $number): void
    {
        $this->writeField($field, Compact::TYPE_I16);
        $this->writeWord($number);
    }

    /**
     * @param int|string $number
     */
    public function writeInt32(int $field, $number): void
    {
        $this->writeField($field, Compact::TYPE_I32);
        $this->writeInt($number);
    }

    /**
     * @param int|string $number
     */
    public function writeInt64(int $field, $number): void
    {
        $this->writeField($field, Compact::TYPE_I64);
        $this->writeLongInt($number);
    }

    public function writeList(int $field, int $type, array $list): void
    {
        $this->writeField($field, Compact::TYPE_LIST);
        $size = count($list);
        if ($size < 0x0f) {
            $this->writeByte(($size << 4) | $type);
        } else {
            $this->writeByte(0xf0 | $type);
            $this->writeVarint($size);
        }

        switch ($type) {
            case Compact::TYPE_TRUE:
            case Compact::TYPE_FALSE:
                foreach ($list as $value) {
                    $this->writeByte($value ? Compact::TYPE_TRUE : Compact::TYPE_FALSE);
                }
                break;
            case Compact::TYPE_BYTE:
                foreach ($list as $number) {
                    $this->writeByte($number);
                }
                break;
            case Compact::TYPE_I16:
                foreach ($list as $number) {
                    $this->writeWord($number);
                }
                break;
            case Compact::TYPE_I32:
                foreach ($list as $number) {
                    $this->writeInt($number);
                }
                break;
            case Compact::TYPE_I64:
                foreach ($list as $number) {
                    $this->writeLongInt($number);
                }
                break;
            case Compact::TYPE_BINARY:
                foreach ($list as $string) {
                    $this->writeVarint(strlen($string));
                    $this->writeBinary($string);
                }
                break;
            default:
                throw new \DomainException("Unsupported list item type {$type}.");
        }
    }

    public function writeStruct(int $field): void
    {
        $this->writeField($field, Compact::TYPE_STRUCT);
        $this->stack[] = $this->field;
        $this->field = 0;
    }

    public function writeMap(int $field, int $keyType, int $valueType, array $map): void
    {
        $this->writeField($field, Compact::TYPE_MAP);
        $this->writeVarint(count($map));
        $this->writeByte(($keyType << 4) | $valueType);
        foreach ($map as $key => $value) {
            $this->writePrimitive($keyType, $key);
            $this->writePrimitive($valueType, $value);
        }
    }

    public function __construct()
    {
        if (PHP_INT_SIZE === 4 && !extension_loaded('gmp')) {
            throw new \RuntimeException('You need to install GMP extension to run this code with x86 PHP build.');
        }
        $this->buffer = '';
        $this->field = 0;
        $this->stack = [];
    }

    public function __toString(): string
    {
        return $this->buffer;
    }
}
