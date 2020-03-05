<?php

declare(strict_types=1);

namespace Fbns\Thrift;

class Field
{
    /** @var int */
    private $type;

    /** @var mixed */
    private $value;

    public function __construct(int $type, $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    public function type(): int
    {
        return $this->type;
    }

    public function value()
    {
        return $this->value;
    }
}
