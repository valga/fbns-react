<?php

declare(strict_types=1);

namespace Fbns\Thrift;

use Fbns\Thrift\Compact\Types;

/**
 * @method Field[] value()
 */
class Struct extends Field
{
    public function __construct(iterable $value)
    {
        parent::__construct(Types::STRUCT, $value);
    }
}
