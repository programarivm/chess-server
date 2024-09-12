<?php

namespace ChessServer\Command\Game;

use ChessServer\Command\AbstractCli;
use ChessServer\Command\Db;

class Cli extends AbstractCli
{
    private Db $db;

    public function __construct(Db $db)
    {
        parent::__construct();

        $this->db = $db;
        $this->commands->attach(new DrawCommand());
        $this->commands->attach(new LeaveCommand());
        $this->commands->attach(new RematchCommand());
        $this->commands->attach(new ResignCommand());
        $this->commands->attach(new TakebackCommand());
        $this->commands->attach(new AcceptPlayRequestCommand());
        $this->commands->attach(new HeuristicCommand());
        $this->commands->attach(new LegalCommand());
        $this->commands->attach(new PlayLanCommand());
        $this->commands->attach(new PlayRavCommand());
        $this->commands->attach(new RandomizerCommand());
        $this->commands->attach(new RestartCommand($db));
        $this->commands->attach(new StartCommand($db));
        $this->commands->attach(new StockfishCommand());
        $this->commands->attach(new TutorFenCommand());
        $this->commands->attach(new EvalNamesCommand());
        $this->commands->attach(new OnlineGamesCommand());
        $this->commands->attach(new UndoCommand());
    }
}