<?php

declare(strict_types=1);

namespace Fbns\Tests\Proto;

use Fbns\Proto\Connect;
use Fbns\Thrift\Compact\Reader;
use Fbns\Thrift\Compact\Writer;
use PHPUnit\Framework\TestCase;

class CapturesTest extends TestCase
{
    public function captures(): iterable
    {
        return new CapturesProvider(__DIR__.'/captures/');
    }

    /**
     * @dataProvider captures
     */
    public function testSerialization(string $filename, string $contents): void
    {
        $reader = new Reader($contents);
        $connect = new Connect($reader());

        $writer = new Writer();
        $binary = $writer($connect);

        $this->assertEquals($contents, $binary);
    }
}
