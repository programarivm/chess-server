<?php

namespace ChessServer\Command\Data;

use ChessServer\Command\AbstractCommand;
use ChessServer\Socket\AbstractSocket;

class SearchCommand extends AbstractCommand
{
    public function __construct()
    {
        $this->name = '/search';
        $this->description = 'Finds up to 25 games matching the criteria.';
        $this->params = [
            'params' => '<string>',
        ];
    }

    public function validate(array $argv)
    {
        return count($argv) - 1 === count($this->params);
    }

    public function run(AbstractSocket $socket, array $argv, int $id)
    {
        $params = json_decode(stripslashes($argv[1]), true);

        $env = [
            'db' => [
                'driver' => $_ENV['DB_DRIVER'],
                'host' => $_ENV['DB_HOST'],
                'database' => $_ENV['DB_DATABASE'],
                'username' => $_ENV['DB_USERNAME'],
                'password' => $_ENV['DB_PASSWORD'],
            ],
        ];

        $this->pool->add(new SearchAsyncTask($params, $env), 81920)
            ->then(function ($result) use ($socket, $id) {
                return $socket->getClientStorage()->send([$id], [
                    $this->name => $result,
                ]);
            });
    }
}
