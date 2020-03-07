<?php

declare(strict_types=1);

namespace Fbns\Thrift;

use Fbns\Thrift\Compact\Types;

class Map extends Field
{
    /** @var int */
    private $keyType;

    /** @var int */
    private $valueType;

    public function __construct(int $keyType, int $valueType, ?iterable $value)
    {
        $this->keyType = $keyType;
        $this->valueType = $valueType;
        parent::__construct(Types::MAP, $value);
    }

    public function keyType(): int
    {
        return $this->keyType;
    }

    public function valueType(): int
    {
        return $this->valueType;
    }
}
