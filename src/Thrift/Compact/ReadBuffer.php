<?php

declare(strict_types=1);

namespace Fbns\Thrift\Compact;

class ReadBuffer
{
    /** @var string */
    private $buffer;

    /** @var int */
    private $length;

    /** @var int */
    private $position;

    public function __construct(string $buffer)
    {
        if (PHP_INT_SIZE === 4 && !extension_loaded('gmp')) {
            throw new \RuntimeException('You have to install GMP extension to run this code with x86 PHP build.');
        }
        $this->buffer = $buffer;
        $this->length = strlen($buffer);
        $this->rewind();
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function isEmpty(): bool
    {
        return $this->position >= $this->length;
    }

    public function readSignedByte(): int
    {
        $result = $this->readUnsignedByte();
        if ($result > 0x7f) {
            $result = 0 - (($result - 1) ^ 0xff);
        }

        return $result;
    }

    public function readUnsignedByte(): int
    {
        return ord($this->buffer[$this->position++]);
    }

    /**
     * @return int|string
     */
    public function readVarint()
    {
        $shift = 0;
        $result = 0;
        if (PHP_INT_SIZE === 4) {
            $result = gmp_init($result, 10);
        }
        while ($this->position < $this->length) {
            $byte = ord($this->buffer[$this->position++]);
            if (PHP_INT_SIZE === 4) {
                $byte = gmp_init($byte, 10);
            }
            $result |= ($byte & 0x7f) << $shift;
            if (PHP_INT_SIZE === 4) {
                $byte = (int) gmp_strval($byte, 10);
            }
            if ($byte >> 7 === 0) {
                break;
            }
            $shift += 7;
        }
        if (PHP_INT_SIZE === 4) {
            $result = gmp_strval($result, 10);
            $intResult = (int) $result;
            if ((string) $intResult === $result) {
                $result = $intResult;
            }
        }

        return $result;
    }

    public function readString(int $length): string
    {
        $result = substr($this->buffer, $this->position, $length);
        $this->position += $length;

        return $result;
    }

    public function readFloatingPoint(string $format, int $bytes): float
    {
        $binary = substr($this->buffer, $this->position, $bytes);
        $this->position += $bytes;

        $data = unpack($format, $binary);

        return $data[1];
    }
}
