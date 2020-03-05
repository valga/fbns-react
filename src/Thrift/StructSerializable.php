<?php

declare(strict_types=1);

namespace Fbns\Thrift;

interface StructSerializable
{
    public function toStruct(): Struct;
}
