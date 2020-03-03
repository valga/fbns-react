<?php

declare(strict_types=1);

namespace Fbns\Client\Thrift;

use Fbns\Client\Thrift\Compact\Types;

class Struct extends Field
{
    public function __construct($value)
    {
        parent::__construct(Types::STRUCT, $value);
    }
}
