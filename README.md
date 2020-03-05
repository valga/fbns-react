# fbns-react

A PHP client for the FBNS, built on top of ReactPHP. Includes generic RTI client implementation.

## Requirements

You have to install the [GMP extension](http://php.net/manual/en/book.gmp.php) to be able to run this code on x86 PHP builds.

## Installation

```sh
composer require valga/fbns-react
```

## Basic Usage

```php
// Set up a Push client.
$loop = \React\EventLoop\Factory::create();
$auth = new \Fbns\Auth\DeviceAuth();
$device = new \Fbns\Device\DefaultDevice(USER_AGENT);
$network = new \Fbns\Network\Wifi();
$client = new \Fbns\Client($loop, $auth, $device, $network, $logger);

// Read saved credentials from the storage.
try {
    $auth->read($storage->get('fbns_auth'));
} catch (\Throwable $e) {
}

// Bind events.
$client
    ->on('connect', static function (string $jsonAuth) use ($client, $auth, $storage, $app) {
        // Update credentials and save them to the storage for future use.
        try {
            $auth->read($jsonAuth);
            $storage->set('fbns_auth', json_encode($auth));
        } catch (\Throwable $e) {
        }
        
        // Register the application.
        $client->register(PACKAGE_NAME, APPLICATION_ID)
            ->then(static function (\Fbns\Push\Registration $registration) use ($app) {
                $app->registerPushToken($registration->getToken());
            });
    })
    ->on('push', static function (\Fbns\Push\Notification $message) use ($app) {
        // Handle received notification payload.
        $app->handlePushNotification($message->getPayload());
    });

// Connect to the broker.
$client->connect(HOSTNAME, PORT);

// Run main loop.
$loop->run();
```

## Advanced Usage

```php
// Set up a proxy.
$connector = new \React\Socket\Connector($loop);
$proxy = new \Clue\React\HttpProxy('username:password@127.0.0.1:3128', $connector);

// Disable SSL verification.
$ssl = new \React\Socket\SecureConnector($proxy, $loop, ['verify_peer' => false, 'verify_peer_name' => false]);

// Enable logging to stdout.
$logger = new \Monolog\Logger('push');
$logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::INFO));

// Set up a Push client.
$client = new \Fbns\Client($loop, $auth, $device, $network, $logger, $connector);

// Persistence.
$client->on('disconnect', static function () {
    // Network connection has been closed. You can reestablish it if you want to.
});
$client->connect(HOSTNAME, PORT)
    ->otherwise(static function () {
        // Connection attempt was unsuccessful, retry with an exponential backoff.
    });
```
