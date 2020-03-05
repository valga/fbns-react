<?php

declare(strict_types=1);

namespace Fbns\Thrift;

use Fbns\Thrift\Compact\Types;

class Struct extends Field
{
    public function __construct($value)
    {
        parent::__construct(Types::STRUCT, $value);
    }
}
