<?php

namespace ChessServer\Command\Game\Async;

use ChessServer\Command\AbstractCommand;
use ChessServer\Socket\AbstractSocket;

class HeuristicCommand extends AbstractCommand
{
    public function __construct()
    {
        $this->name = '/heuristic';
        $this->description = 'Balance of a chess heuristic.';
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

        $this->pool->add(new HeuristicAsyncTask($params))
            ->then(function ($result) use ($socket, $id) {
                return $socket->getClientStorage()->send([$id], [
                    $this->name => $result,
                ]);
            });
    }
}