<?php

declare(strict_types=1);

namespace Fbns\Tests;

use Fbns\Auth;
use Fbns\Auth\DeviceAuth;
use Fbns\Client;
use Fbns\Device;
use Fbns\Device\DefaultDevice;
use Fbns\Network;
use Fbns\Network\Lte;
use Fbns\Network\Wifi;
use Fbns\Push\Registration;
use PHPUnit\Framework\TestCase;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\Socket\Connector;
use React\Socket\ConnectorInterface;
use React\Socket\SecureConnector;

class IntegrationTest extends TestCase
{
    private const HOSTNAME = 'fbns.abyrga.ru';
    private const USER_AGENT = '[FBAN/MQTT;FBAV/130.0.0.31.121;FBBV/200396014;FBDM/{density=3.0,width=1080,height=1920};FBLC/en_GB;FBCR/;FBMF/Xiaomi;FBBD/Xiaomi;FBPN/com.instagram.android;FBDV/Mi 8;FBSV/10;FBLR/0;FBBK/1;FBCA/arm64-v8a:;]';

    private const MAX_EXECUTION_TIME = 8;

    /** @var LoopInterface */
    private $loop;

    /** @var ConnectorInterface */
    private $connector;

    /** @var string */
    private $sessionsPath;

    protected function setUp(): void
    {
        $sessionsPath = __DIR__.'/sessions/';
        if (!is_dir($sessionsPath) || !is_writable($sessionsPath)) {
            $this->markTestSkipped('You have to create a "sessions" directory to run integration tests.');

            return;
        }
        $this->sessionsPath = $sessionsPath;

        $this->loop = Factory::create();
        $this->connector = new SecureConnector(new Connector($this->loop), $this->loop, [
            'verify_peer_name' => false,
        ]);
        $this->loop->addTimer(self::MAX_EXECUTION_TIME, function () {
            $this->loop->stop();
            $this->fail('Maximum execution time exceeded.');
        });
    }

    private function failAndStopOnNextTick(\Throwable $e): void
    {
        $this->loop->futureTick(function () use ($e) {
            $this->loop->stop();
            $this->fail($e->getMessage());
        });
    }

    private function connectAndRegister(Auth $auth, Device $device, Network $network, string $session): void
    {
        $client = new Client($this->loop, $auth, $device, $network, null, $this->connector);
        $client->connect(self::HOSTNAME, 443)
            ->then(function (?string $authJson) use ($auth, $client, $session) {
                $this->assertTrue($client->isConnected());
                $this->assertNotEmpty($authJson);
                $oldAuth = json_encode($auth);
                $auth->read($authJson);
                $newAuth = json_encode($auth);
                $this->assertEquals($oldAuth, $newAuth);
                file_put_contents($session, $newAuth);

                $client->register('com.instagram.android', '567067343352427')
                    ->then(function (Registration $registration) use ($client) {
                        $this->assertNotEmpty($registration->getToken());
                        $client->disconnect()
                            ->then(function () {
                                $this->loop->stop();
                            })
                            ->otherwise(function (\Throwable $e) {
                                $this->failAndStopOnNextTick($e);
                            });
                    })
                    ->otherwise(function (\Throwable $e) {
                        $this->failAndStopOnNextTick($e);
                    });
            })
            ->otherwise(function (\Throwable $e) {
                $this->failAndStopOnNextTick($e);
            });
        $this->loop->run();
    }

    public function testFreshSession(): void
    {
        $device = new DefaultDevice(self::USER_AGENT);
        $session = $this->sessionsPath.sha1($device->userAgent()).'.json';
        if (file_exists($session)) {
            $filename = basename($session);
            $this->markTestSkipped("Remove \"{$filename}\" from \"sessions\" directory to run fresh session test.");

            return;
        }
        $auth = new DeviceAuth();
        $this->connectAndRegister($auth, $device, new Wifi(), $session);
    }

    public function testReusedSession(): void
    {
        $device = new DefaultDevice(self::USER_AGENT);
        $session = $this->sessionsPath.sha1($device->userAgent()).'.json';
        if (!file_exists($session)) {
            $filename = basename($session);
            $this->markTestSkipped("Save session into \"{$filename}\" from \"sessions\" directory to run fresh session test.");

            return;
        }
        $auth = new DeviceAuth();
        $auth->read(file_get_contents($session));
        $this->connectAndRegister($auth, $device, new Lte(), $session);
    }

    protected function tearDown(): void
    {
        $this->loop->stop();
    }
}
