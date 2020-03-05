<?php

declare(strict_types=1);

namespace Fbns\Client\Mqtt;

use Psr\Log\LoggerInterface;

class DefaultTopicMapper implements TopicMapper
{
    /** @var int[] */
    private $map;

    /** @var string[] */
    private $reversed;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(array $map, LoggerInterface $logger)
    {
        $this->map = $map;
        $this->logger = $logger;
        $this->reversed = array_flip($this->map);
    }

    public function map(string $topic): string
    {
        if (!isset($this->map[$topic])) {
            $this->logger->debug("Unknown topic '{$topic}'.");

            return $topic;
        }
        $id = (string) $this->map[$topic];
        $this->logger->debug("Topic '{$topic}' has been mapped to '{$id}'.");

        return $id;
    }

    public function unmap(string $id): string
    {
        if (!isset($this->reversed[$id])) {
            $this->logger->debug("Unknown topic ID '{$id}'.");

            return $id;
        }
        $topic = $this->reversed[$id];
        $this->logger->debug("ID '{$id}' has been mapped to '{$topic}'.");

        return $topic;
    }
}
