<?php

namespace ChessServer\Cli\Ratchet;

use ChessServer\Command\CommandParser;
use ChessServer\Command\Data\CommandContainer;
use ChessServer\Command\Data\Db;
use ChessServer\Socket\RatchetClientStorage;
use ChessServer\Socket\RatchetDataWebSocket;
use Dotenv\Dotenv;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use React\Socket\LimitingServer;
use React\Socket\Server;
use React\Socket\SecureServer;

require __DIR__  . '/../../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__.'/../../');
$dotenv->load();

$db = new Db([
   'driver' => $_ENV['DB_DRIVER'],
   'host' => $_ENV['DB_HOST'],
   'database' => $_ENV['DB_DATABASE'],
   'username' => $_ENV['DB_USERNAME'],
   'password' => $_ENV['DB_PASSWORD'],
]);

$logger = new Logger('log');
$logger->pushHandler(new StreamHandler(__DIR__.'/../../storage' . '/data.log', Logger::INFO));

$parser = new CommandParser(new CommandContainer($db));

$clientStorage = new RatchetClientStorage($logger);

$webSocket = (new RatchetDataWebSocket($parser))->init($clientStorage);

$loop = $webSocket->getLoop();

$server = new Server("{$_ENV['WSS_ADDRESS']}:{$_ENV['WSS_DATA_PORT']}", $loop);

$secureServer = new SecureServer($server, $loop, [
    'local_cert'  => __DIR__  . '/../../ssl/fullchain.pem',
    'local_pk' => __DIR__  . '/../../ssl/privkey.pem',
    'verify_peer' => false,
]);

$limitingServer = new LimitingServer($secureServer, 50);

$httpServer = new HttpServer(new WsServer($webSocket));

$ioServer = new IoServer($httpServer, $limitingServer, $loop);

$ioServer->run();
