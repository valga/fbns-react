<?php

declare(strict_types=1);

namespace Fbns\Thrift\Compact;

/**
 * @see https://thrift.apache.org/
 */
class Types
{
    public const STOP = 0x00;
    public const TRUE = 0x01;
    public const FALSE = 0x02;
    public const BYTE = 0x03;
    public const I16 = 0x04;
    public const I32 = 0x05;
    public const I64 = 0x06;
    public const DOUBLE = 0x07;
    public const BINARY = 0x08;
    public const LIST = 0x09;
    public const SET = 0x0A;
    public const MAP = 0x0B;
    public const STRUCT = 0x0C;
    public const FLOAT = 0x0D;
}
