<?php

declare(strict_types=1);

namespace Fbns\Push;

use Fbns\Json;

class Registration
{
    /** @var string */
    private $json;

    /** @var string */
    private $packageName;

    /** @var string */
    private $token;

    /** @var string */
    private $error;

    private function parseJson(string $json)
    {
        $data = Json::decode($json);
        $this->json = $json;

        if (isset($data->pkg_name)) {
            $this->packageName = (string) $data->pkg_name;
        }
        if (isset($data->token)) {
            $this->token = (string) $data->token;
        }
        if (isset($data->error)) {
            $this->error = (string) $data->error;
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

    public function getPackageName(): string
    {
        return $this->packageName;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getError(): string
    {
        return $this->error;
    }
}
