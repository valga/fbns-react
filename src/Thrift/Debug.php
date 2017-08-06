<?php

namespace Fbns\Client\Thrift;

class Debug extends Reader
{
    /**
     * @param string $context
     * @param int    $field
     * @param mixed  $value
     */
    private function handler($context, $field, $value)
    {
        if (strlen($context)) {
            $field = $context.'/'.$field;
        }
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        } elseif (is_array($value)) {
            $value = '['.implode(', ', $value).']';
        } else {
            $value = (string) $value;
        }
        printf('%s: %s%s', $field, $value, PHP_EOL);
    }

    /**
     * Debug constructor.
     *
     * @param string $buffer
     */
    public function __construct($buffer = '')
    {
        parent::__construct($buffer, function ($context, $field, $value) {
            $this->handler($context, $field, $value);
        });
    }
}
