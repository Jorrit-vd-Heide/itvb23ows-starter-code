<?php

namespace src\Controllers;

include_once 'playerController.php';
include_once 'views/util.php';
include_once 'models/game.php';
include_once 'gameStateController.php';

trait MoveTile {
    public function moveTile($piece, $to) {
        $this->clearError();
        $hand = $this->getHand($this->getActivePlayer());

        if (!isset($this->board[$piece])) {
            $this->setError('Board position is empty');
        } elseif ($this->board[$piece][count($this->board[$piece]) - 1][0] != $this->getActivePlayer()) {
            $this->setError("Tile is not owned by player");
        } elseif ($hand['Q']) {
            $this->setError("Queen bee is not played");
        }
        if ($this->hasError()) return;

        $tile = array_pop($this->board[$piece]);
        if (!$this->hasNeighbour($to, $this->board)) {
            $this->setError("Move would split hive");
        } else {
            $all = array_keys($this->board);
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
            if (isset($this->board[$piece])) {
                array_push($this->board[$piece], $tile);
            } else {
                $this->board[$piece] = [$tile];
            }
        } else {
            if (isset($this->board[$to])) {
                array_push($this->board[$to], $tile);
            } else {
                $this->board[$to] = [$tile];
            }
            $this->changePlayer();
            $stmt = $this->database->prepare('insert into moves (game_id, type, move_from, move_to, previous_id, state) values (?, "move", ?, ?, ?, ?)');
            $stmt->bind_param('issis', $this->game_id, $piece, $to, $this->last_move, $this->setState());
            $stmt->execute();
            $this->last_move = $this->database->insert_id;
        }
    }
}
