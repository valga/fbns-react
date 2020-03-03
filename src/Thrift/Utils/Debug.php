<?php

declare(strict_types=1);

namespace Fbns\Client\Thrift\Utils;

use Fbns\Client\Thrift\Compact\Reader;
use Fbns\Client\Thrift\Field;
use Fbns\Client\Thrift\Map;
use Fbns\Client\Thrift\Series;
use Fbns\Client\Thrift\Struct;

class Debug
{
    private const PADDING = '    ';

    /** @var Reader */
    private $reader;

    public function __construct($buffer)
    {
        $this->reader = new Reader($buffer);
    }

    public function __invoke()
    {
        $reader = $this->reader;
        $this->debugStruct($reader()->value());
    }

    private function debugStruct(iterable $struct, string $padding = ''): void
    {
        foreach ($struct as $idx => $field) {
            printf('%s%d (%02x): ', $padding, $idx, $field->type());
            $this->debugValue($field, $padding);
            echo "\n";
        }
    }

    private function debugValue($value, string $padding): void
    {
        switch (true) {
            case $value instanceof Series:
                printf("%02x [\n", $value->itemType());
                foreach ($value->value() as $item) {
                    $this->debugValue($item, $padding.self::PADDING);
                    echo ",\n";
                }
                echo "{$padding}]";
                break;
            case $value instanceof Map:
                printf("%02x => %02x [\n", $value->keyType(), $value->valueType());
                foreach ($value->value() as $key => $item) {
                    $this->debugValue($key, $padding.self::PADDING);
                    echo ' => ';
                    $this->debugValue($item, '');
                    echo ",\n";
                }
                echo "{$padding}]";
                break;
            case $value instanceof Struct:
                echo "{\n";
                $this->debugStruct($value->value(), $padding.self::PADDING);
                echo "{$padding}}";
                break;
            case $value instanceof Field:
                $this->debugValue($value->value(), '');
                break;
            case is_string($value):
                echo "{$padding}\"{$value}\"";
                break;
            case is_bool($value):
                echo $padding, $value ? 'true' : 'false';
                break;
            default:
                echo $padding, (string) $value;
        }
    }
}
