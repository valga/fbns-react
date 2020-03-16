<?php

declare(strict_types=1);

namespace Fbns;

use BinSoul\Net\Mqtt\Connection as BaseConnection;
use BinSoul\Net\Mqtt\Message;


interface Connection extends BaseConnection
{
    public function toThrift(): string;

    public function getProtocol(): int;

    public function getClientID(): string;

    public function isCleanSession(): bool;

    public function getUsername(): string;

    public function getPassword(): string;

    public function getWill(): ?Message;

    public function getKeepAlive(): int;

    public function withProtocol(int $protocol): BaseConnection;

    public function withClientID(string $clientID): BaseConnection;

    public function withCredentials(string $username, string $password): BaseConnection;

    public function withWill(Message $will = null): BaseConnection;

    public function withKeepAlive(int $timeout): BaseConnection;
}
