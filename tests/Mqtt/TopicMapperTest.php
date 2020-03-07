<?php

declare(strict_types=1);

namespace Fbns\Tests\Mqtt;

use Fbns\Mqtt\TopicMapper;
use Fbns\Push\FbnsTopics;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class TopicMapperTest extends TestCase
{
    /** @var TopicMapper */
    private $mapper;

    /** @var LoggerInterface */
    private $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->mapper = new FbnsTopics($this->logger);
    }

    public function testKnownTopicMustMap(): void
    {
        $this->logger->expects($this->once())
            ->method('debug');
        $this->assertEquals('76', $this->mapper->map('/fbns_msg'));
    }

    public function testUnknownTopicMustRemainUnmapped(): void
    {
        $this->logger->expects($this->once())
            ->method('debug');
        $this->assertEquals('/fbns_not_msg', $this->mapper->map('/fbns_not_msg'));
    }

    public function testKnownTopicMustUnmap(): void
    {
        $this->logger->expects($this->once())
            ->method('debug');
        $this->assertEquals('/fbns_msg', $this->mapper->unmap('76'));
    }

    public function testUnknownTopicIdMustRemainUnmapped(): void
    {
        $this->logger->expects($this->once())
            ->method('debug');
        $this->assertEquals('123', $this->mapper->unmap('123'));
    }

    public function testFullyQualifiedTopicMustRemainUnmapped(): void
    {
        $this->logger->expects($this->never())
            ->method('debug');
        $this->assertEquals('/test', $this->mapper->unmap('/test'));
    }
}
