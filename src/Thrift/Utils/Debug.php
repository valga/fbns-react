<?php

declare(strict_types=1);

namespace Fbns\Thrift\Utils;

use Fbns\Thrift\Compact\Reader;
use Fbns\Thrift\Field;
use Fbns\Thrift\Map;
use Fbns\Thrift\Series;
use Fbns\Thrift\Struct;

class Debug
{
    private const PADDING = '    ';

    public function __invoke(string $buffer)
    {
        $reader = new Reader($buffer);
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

    private function debugSeries(Series $value, string $padding): void
    {
        printf("%02x [\n", $value->itemType());
        foreach ($value->value() as $item) {
            $this->debugValue($item, $padding.self::PADDING);
            echo ",\n";
        }
        echo "{$padding}]";
    }

    private function debugMap(Map $value, string $padding): void
    {
        printf("%02x => %02x [\n", $value->keyType(), $value->valueType());
        foreach ($value->value() as $key => $item) {
            $this->debugValue($key, $padding.self::PADDING);
            echo ' => ';
            $this->debugValue($item, '');
            echo ",\n";
        }
        echo "{$padding}]";
    }

    private function debugValue($value, string $padding): void
    {
        switch (true) {
            case $value instanceof Series:
                $this->debugSeries($value, $padding);
                break;
            case $value instanceof Map:
                $this->debugMap($value, $padding);
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
