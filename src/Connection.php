<?php

declare(strict_types=1);

namespace Fbns\Client;

use BinSoul\Net\Mqtt\Connection as BaseConnection;

interface Connection extends BaseConnection
{
    public function toThrift(): string;
}
