<?php

namespace ChessServer\Command\Data;

use ChessServer\Db;
use Spatie\Async\Task;

class RankingAsyncTask extends Task
{
    private array $env;

    private Db $db;

    public function __construct(array $env)
    {
        $this->env = $env;
    }

    public function configure()
    {
        $this->db = new Db($this->env['db']);
    }

    public function run()
    {
        $sql = "SELECT username, elo FROM users WHERE lastLoginAt IS NOT NULL ORDER BY elo DESC LIMIT 20";

        return $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }
}