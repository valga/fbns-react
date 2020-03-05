<?php

declare(strict_types=1);

namespace Fbns;

interface Auth
{
    public function getClientId(): string;

    public function getClientType(): string;

    public function getUserId(): int;

    public function getPassword(): string;

    public function getDeviceId(): string;

    public function getDeviceSecret(): string;
}
