<?php

namespace Fbns\Client\Thrift;

class Debug extends Reader
{
    /**
     * @param mixed $value
     */
    private function formatValue($value): string
    {
        switch (true) {
            case is_bool($value):
                return $value ? 'true' : 'false';
            case is_string($value):
                return "\"{$value}\"";
            default:
                return (string) $value;
        }
    }

    private function formatList(array $values): string
    {
        return '['.implode(', ', array_map([$this, 'formatValue'], $values)).']';
    }

    private function formatMap(array $map): string
    {
        $pairs = [];
        foreach ($map as $key => $value) {
            $pairs[] = "\t".$this->formatValue($key).' => '.$this->formatValue($value);
        }

        return "{\n".implode(",\n", $pairs)."\n}";
    }

    /**
     * @param mixed $value
     */
    private function handler(string $context, int $field, $value, int $type): void
    {
        if ($context !== '') {
            $field = $context.'/'.$field;
        }
        switch (true) {
            case is_array($value):
                $formatted = $type === Compact::TYPE_MAP
                    ? $this->formatMap($value)
                    : $this->formatList($value);
                break;
            default:
                $formatted = $this->formatValue($value);
        }

        printf('%s (%02x): %s%s', $field, $type, $formatted, PHP_EOL);
    }

    /**
     * Debug constructor.
     *
     * @param string $buffer
     */
    public function __construct($buffer = '')
    {
        parent::__construct($buffer, function ($context, $field, $value, $type) {
            $this->handler($context, $field, $value, $type);
        });
    }
}
