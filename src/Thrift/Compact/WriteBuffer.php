<?php

declare(strict_types=1);

namespace Fbns\Thrift\Compact;

class WriteBuffer
{
    /** @var string */
    private $buffer;

    public function __construct()
    {
        if (PHP_INT_SIZE === 4 && !extension_loaded('gmp')) {
            throw new \RuntimeException('You have to install GMP extension to run this code with x86 PHP build.');
        }
        $this->buffer = '';
    }

    public function __toString(): string
    {
        return $this->buffer;
    }

    public function clear(): void
    {
        $this->buffer = '';
    }

    public function writeByte(int $number): void
    {
        $this->buffer .= chr($number);
    }

    public function writeBinary(string $data): void
    {
        $this->buffer .= $data;
    }

    /**
     * @param int|string $number
     */
    public function writeVarint($number): void
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

    public function writeFloatingPoint(float $number, string $format): void
    {
        $this->buffer .= pack($format, $number);
    }
}
