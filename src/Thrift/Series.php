<?php

declare(strict_types=1);

namespace Fbns\Client\Thrift;

use Fbns\Client\Thrift\Compact\Types;

class Series extends Field
{
    /** @var int */
    private $itemType;

    public function __construct(int $itemType, $value)
    {
        $this->itemType = $itemType;
        parent::__construct(Types::LIST, $value);
    }

    public function itemType(): int
    {
        return $this->itemType;
    }
}
