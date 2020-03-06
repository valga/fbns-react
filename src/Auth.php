<?php

declare(strict_types=1);

namespace Fbns;

interface Auth
{
    public function getClientId(): string;

    public function getClientType(): string;

    /**
     * @return int|string
     */
    public function getUserId();

    public function getPassword(): string;

    public function getDeviceId(): string;

    public function getDeviceSecret(): string;
}
