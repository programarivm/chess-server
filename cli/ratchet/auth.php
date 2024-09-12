<?php

namespace ChessServer\Cli\Ratchet;

use ChessServer\Command\Db;
use ChessServer\Command\Parser;
use ChessServer\Command\Auth\Cli;
use ChessServer\Socket\Ratchet\ClientStorage;
use ChessServer\Socket\Ratchet\AuthWebSocket;
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

$logger = new Logger('auth');
$logger->pushHandler(new StreamHandler(__DIR__.'/../../storage' . '/auth.log', Logger::INFO));

$parser = new Parser(new Cli($db));

$clientStorage = new ClientStorage($logger);

$webSocket = (new AuthWebSocket($parser))->init($clientStorage);

$server = new Server("{$_ENV['WSS_ADDRESS']}:{$_ENV['WSS_AUTH_PORT']}", $webSocket->getLoop());

$secureServer = new SecureServer($server, $webSocket->getLoop(), [
    'local_cert'  => __DIR__  . '/../../ssl/fullchain.pem',
    'local_pk' => __DIR__  . '/../../ssl/privkey.pem',
    'verify_peer' => false,
]);

$limitingServer = new LimitingServer($secureServer, 50);

$httpServer = new HttpServer(new WsServer($webSocket));

$ioServer = new IoServer($httpServer, $limitingServer, $webSocket->getLoop());

$ioServer->run();
