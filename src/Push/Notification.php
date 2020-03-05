<?php

declare(strict_types=1);

namespace Fbns\Push;

use Fbns\Json;

class Notification
{
    /** @var string */
    private $json;

    /** @var string */
    private $token;

    /** @var string */
    private $connectionKey;

    /** @var string */
    private $packageName;

    /** @var string */
    private $collapseKey;

    /** @var string */
    private $payload;

    /** @var string */
    private $notificationId;

    /** @var string */
    private $isBuffered;

    private function parseJson(string $json): void
    {
        $data = Json::decode($json);
        $this->json = $json;

        if (isset($data->token)) {
            $this->token = (string) $data->token;
        }
        if (isset($data->ck)) {
            $this->connectionKey = (string) $data->ck;
        }
        if (isset($data->pn)) {
            $this->packageName = (string) $data->pn;
        }
        if (isset($data->cp)) {
            $this->collapseKey = (string) $data->cp;
        }
        if (isset($data->fbpushnotif)) {
            $this->payload = (string) $data->fbpushnotif;
        }
        if (isset($data->nid)) {
            $this->notificationId = (string) $data->nid;
        }
        if (isset($data->bu)) {
            $this->isBuffered = (string) $data->bu;
        }
    }

    public function __construct(string $json)
    {
        $this->parseJson($json);
    }

    public function __toString(): string
    {
        return $this->json;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getConnectionKey(): string
    {
        return $this->connectionKey;
    }

    public function getPackageName(): string
    {
        return $this->packageName;
    }

    public function getCollapseKey(): string
    {
        return $this->collapseKey;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function getNotificationId(): string
    {
        return $this->notificationId;
    }

    public function getIsBuffered(): string
    {
        return $this->isBuffered;
    }
}
