<?php

declare(strict_types=1);

namespace Fbns;

use BinSoul\Net\Mqtt\Connection as BaseConnection;
use BinSoul\Net\Mqtt\Message;


interface Connection extends BaseConnection
{
    public function toThrift(): string;
}
