<?php

namespace ChessServer\Command;

use ChessServer\Socket\ChesslaBlab;
use ChessServer\Exception\InternalErrorException;
use ChessServer\Game\PlayMode;

class LeaveCommand extends AbstractCommand
{
    const ACTION_ACCEPT    = 'accept';

    public function __construct()
    {
        $this->name = '/leave';
        $this->description = 'Allows to leave a game.';
        $this->params = [
            // mandatory param
            'action' => [
                self::ACTION_ACCEPT,
            ],
        ];
    }

    public function validate(array $argv)
    {
        if (in_array($argv[1], $this->params['action'])) {
            return count($argv) - 1 === count($this->params);
        }

        return false;
    }

    public function run(ChesslaBlab $socket, array $argv, int $resourceId)
    {
        $gameMode = $socket->getGameModeStorage()->getByResourceId($resourceId);

        if (!$gameMode) {
            throw new InternalErrorException();
        }

        if (is_a($gameMode, PlayMode::class)) {
            return $socket->sendToMany($gameMode->getResourceIds(), [
                $this->name => [
                    'action' => $argv[1],
                ],
            ]);
        }
    }
}
