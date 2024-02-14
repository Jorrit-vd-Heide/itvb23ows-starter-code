<?php

include_once '/var/www/html/Models/hiveGameModel.php';
include_once '/var/www/html/Views/hiveGameView.php';
include_once '/var/www/html/Views/util.php';

class HiveGameController {
    private $model;
    private $view;

    public function __construct($model, $view) {
        $this->model = $model;
        $this->view = $view;
    }

    public function hasError() {
        return $this->model->error !== null;
    }

    public function getError() {
        return $this->model->error;
    }

    public function setError($error) {
        $this->model->error = $error;
    }

    public function clearError() {
        $this->model->error = null;
    }

    public function getHand($player) {
        $hand = $this->model->hand[$player];
        return array_filter($hand, function ($ct) { return $ct > 0; });
    }

    public function setHand($player, $hand) {
        $this->model->hand[$player] = $hand;
    }

    public function getPlayerName($player) {
        return $player == 0 ? 'White' : 'Black';
    }

    public function getActivePlayer() {
        return $this->model->activePlayer;
    }

    public function getPossibleMoves() {
        $to = [];
        foreach ($GLOBALS['OFFSETS'] as $pq) {
            foreach (array_keys($this->model->board) as $pos) {
                $pq2 = explode(',', $pos);
                $to[] = ($pq[0] + $pq2[0]).','.($pq[1] + $pq2[1]);
            }
        }
        $to = array_unique($to);
        if (!count($to)) $to[] = '0,0';
        return $to;
    }

    public function playTile($piece, $to) {
        $this->clearError();
        $hand = $this->getHand($this->getActivePlayer());
        
        if (!$hand[$piece]) {
            $this->setError("Player does not have tile");
        } elseif (isset($this->model->board[$to])) {
            $this->setError('Board position is not empty');
        } elseif (count($this->model->board) && !hasNeighBour($to, $this->model->board)) {
            $this->setError("board position has no neighbour");
        } elseif (array_sum($hand) < 11 && !neighboursAreSameColor($this->getActivePlayer(), $to, $this->model->board)) {
            $this->setError("Board position has opposing neighbour");
        } elseif (array_sum($hand) <= 8 && isset($hand['Q']) && $hand['Q'] > 0 && $piece != 'Q')  {
            $this->setError("Must play queen bee");
        }
        if ($this->hasError()) return;

        $this->model->board[$to] = [[$this->getActivePlayer(), $piece]];
        $this->model->hand[$this->getActivePlayer()][$piece]--;
        $this->model->activePlayer = $this->getActivePlayer() == 0 ? 1 : 0;
        $stmt = $this->model->database->prepare('insert into moves (game_id, type, move_from, move_to, previous_id, state) values (?, ?, "play", ?, ?, ?)');
        $setState = $this->model->setState();
        $stmt->bind_param('issis', $this->model->game_id, $piece, $to, $this->model->last_move, $setState);
        $stmt->execute();
        $this->model->last_move = $this->model->database->insert_id;
        $this->model->move_nr++;
    }

    public function moveTile($piece, $to) {
        $this->clearError();
        $hand = $this->getHand($this->getActivePlayer());

        if (!isset($this->model->board[$piece])) {
            $this->setError('Board position is empty');
        } elseif ($this->model->board[$piece][count($this->model->board[$piece]) - 1][0] != $this->getActivePlayer()) {
            $this->setError("Tile is not owned by player");
        } elseif ($hand['Q']) {
            $this->setError("Queen bee is not played");
        }
        if ($this->hasError()) return;

        $tile = array_pop($this->model->board[$piece]);
        if (!hasNeighbour($to, $this->model->board)) {
            $this->setError("Move would split hive");
        } else {
            $all = array_keys($this->model->board);
            $queue = [array_shift($all)];
            while ($queue) {
                $next = explode(',', array_shift($queue));
                foreach ($GLOBALS['OFFSETS'] as $pq) {
                    list($p, $q) = $pq;
                    $p += $next[0];
                    $q += $next[1];
                    if (in_array("$p,$q", $all)) {
                        $queue[] = "$p,$q";
                        $all = array_diff($all, ["$p,$q"]);
                    }
                }
            }
            if ($all) {
                $this->setError("Move would split hive");
            } else {
                if ($piece == $to) {
                    $this->setError('Tile must move');
                } elseif (isset($this->board[$to]) && $tile[1] != "B") {
                    $this->setError('Tile not empty');
                } elseif ($tile[1] == "Q" || $tile[1] == "B") {
                    if (!$this->slide($this->board, $piece, $to)) {
                        $this->setError('Tile must slide');
                    }
                }
            }
        }
        if ($this->hasError()) {
            if (isset($this->model->board[$piece])) {
                array_push($this->model->board[$piece], $tile);
            } else {
                $this->model->board[$piece] = [$tile];
            }
        } else {
            if (isset($this->model->board[$to])) {
                array_push($this->model->board[$to], $tile);
            } else {
                $this->board[$to] = [$tile];
            }
            $this->changePlayer();
            $stmt = $this->model->database->prepare('insert into moves (game_id, type, move_from, move_to, previous_id, state) values (?, "move", ?, ?, ?, ?)');
            $stmt->bind_param('issis', $this->game_id, $piece, $to, $this->last_move, $this->setState());
            $stmt->execute();
            $this->model->last_move = $this->database->insert_id;
        }
    }

    public function pass() {
        $stmt = $this->database->prepare('insert into moves (game_id, type, move_from, move_to, previous_id, state) values (?, "pass", null, null, ?, ?)');
        $stmt->bind_param('iis', $this->game_id, $this->last_move, $this->serializeState());
        $stmt->execute();
        $this->last_move = $this->database->insert_id;
        $this->setOtherPlayer();
    }

    public function undo() {
        $stmt = $this->database->prepare('SELECT * FROM moves WHERE id = ? AND game_id = ?');
        $stmt->bind_param('ii', $this->last_move, $this->game_id);
        $stmt->execute();
        $this->last_move = $result[5];
        $this->loadState($result[6]);
    }

    public function restart() {
        $this->model->board = [];
        $this->model->hand = [0 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3], 1 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3]];
        $this->model->activePlayer = 0;
        $this->model->last_move = 0;
        $stmt = $this->model->database->prepare('INSERT INTO games VALUES ()');
        $stmt->execute();
        $this->model->game_id = $this->model->database->insert_id;
    }
}
