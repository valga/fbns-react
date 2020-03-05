<?php

declare(strict_types=1);

namespace Fbns\Mqtt;

interface TopicMapper
{
    public function map(string $topic): string;

    public function unmap(string $id): string;
}
