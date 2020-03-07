<?php

declare(strict_types=1);

namespace Fbns\Thrift;

use Fbns\Thrift\Compact\Types;

class Series extends Field
{
    /** @var int */
    private $itemType;

    public function __construct(int $itemType, ?iterable $value)
    {
        $this->itemType = $itemType;
        parent::__construct(Types::LIST, $value);
    }

    public function itemType(): int
    {
        return $this->itemType;
    }
}
