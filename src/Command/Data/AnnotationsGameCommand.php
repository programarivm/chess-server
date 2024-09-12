<?php

namespace ChessServer\Command\Data;

use ChessServer\Command\AbstractCommand;
use ChessServer\Command\Db;
use ChessServer\Socket\AbstractSocket;

class AnnotationsGameCommand extends AbstractCommand
{
    const ANNOTATIONS_GAMES_FILE = 'annotations_games.json';

    public function __construct(Db $db)
    {
        parent::__construct($db);

        $this->name = '/annotations_game';
        $this->description = 'Annotated chess games.';
    }

    public function validate(array $argv)
    {
        return count($argv) - 1 === 0;
    }

    public function run(AbstractSocket $socket, array $argv, int $id)
    {
        $contents = file_get_contents(AbstractSocket::DATA_FOLDER.'/'.self::ANNOTATIONS_GAMES_FILE);

        $arr = json_decode($contents);

        return $socket->getClientStorage()->sendToOne($id, [
            $this->name => $arr,
        ]);
    }
}