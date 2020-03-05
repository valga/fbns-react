<?php

declare(strict_types=1);

namespace Fbns\Push;

use Fbns\Mqtt\DefaultTopicMapper;
use Fbns\Mqtt\TopicMapper;
use Psr\Log\LoggerInterface;

class FbnsTopics implements TopicMapper
{
    private const MAP = [
        '/fbns_msg' => 76,
        '/fbns_reg_req' => 79,
        '/fbns_reg_resp' => 80,
        '/fbns_unreg_req' => 82,
        '/fbns_unreg_resp' => 83,
        '/fbns_msg_hp' => 137,
        '/fbns_msg_ack' => 180,
        '/fbns_exp_logging' => 231,
    ];

    /** @var TopicMapper */
    private $mapper;

    public function __construct(LoggerInterface $logger)
    {
        $this->mapper = new DefaultTopicMapper(self::MAP, $logger);
    }

    public function map(string $topic): string
    {
        return $this->mapper->map($topic);
    }

    public function unmap(string $id): string
    {
        return $this->mapper->unmap($id);
    }
}
