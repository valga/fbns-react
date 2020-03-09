<?php

declare(strict_types=1);

namespace Fbns\Tests\Thrift\Compact;

use Fbns\Thrift\Compact\Reader;
use Fbns\Thrift\Compact\Types;
use Fbns\Thrift\Compact\Writer;
use Fbns\Thrift\Field;
use Fbns\Thrift\Struct;
use Fbns\Thrift\StructSerializable;
use PHPUnit\Framework\TestCase;

class IntsTest extends TestCase
{
    public function data(): \Generator
    {
        yield [Types::BYTE, -128, 127];
        yield [Types::I16, -32768, 32767];
        yield [Types::I32, ~2147483647, 2147483647];
        if (PHP_INT_SIZE === 4) {
            yield [Types::I64, '-9223372036854775808', '9223372036854775807'];
        } else {
            yield [Types::I64, ~9223372036854775807, 9223372036854775807];
        }
    }

    /**
     * @dataProvider data
     *
     * @param int|string $min
     * @param int|string $max
     */
    public function testEdges(int $type, $min, $max): void
    {
        $source = new class($type, $min, $max) implements StructSerializable {
            private $type;
            private $min;
            private $max;

            public function __construct(int $type, $min, $max)
            {
                $this->type = $type;
                $this->min = $min;
                $this->max = $max;
            }

            public function toStruct(): Struct
            {
                return new Struct((function () {
                    yield 1 => new Field($this->type, $this->min);
                    yield 2 => new Field($this->type, $this->max);
                })());
            }
        };

        $writer = new Writer();
        $binary = $writer($source);

        $reader = new Reader($binary);
        foreach ($reader()->value() as $id => $field) {
            switch ($id) {
                case 1:
                    $this->assertEquals($min, $field->value());
                    break;
                case 2:
                    $this->assertEquals($max, $field->value());
                    break;
                default:
                    $this->fail("Unexpected field {$id}.");
            }
        }
    }
}
