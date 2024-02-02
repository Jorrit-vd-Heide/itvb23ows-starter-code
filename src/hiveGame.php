<?php

include_once 'util.php';

class HiveGame {
    public $board = array();
    private $hand = array();
    private $gameId = 0;
    private $error = null;
    private $database;
    public $activePlayer = 0;
    private $previousMove = 0;

    public function build($database) {
        $this->database = $database;
    }

    public function hasError() {
        return $this->error !== null;
    }

    public function getError() {
        return $this->error;
    }

    public function setError($error) {
        $this->error = $error;
    }

    public function clearError() {
        $this->error = null;
    }

    public function currentSession() {
        if (!isset($_SESSION['board'])) {
            throw new Exception('Can not get board state');
        }
        $this->board = $_SESSION['board'];
        $this->hand = $_SESSION['hand'];
        $this->previousMove = $_SESSION['previousMove'];
        $this->gameId = $_SESSION['gameId'];
        $this->error = $_SESSION['error'];
        $this->activePlayer = $_SESSION['player'];
    }

    public function setState() {
        return serialize([$this->hand, $this->board, $this->activePlayer]);
    }

    public function getState($state) {
        list($this->hand, $this->board, $this->activePlayer) = unserialize($state);
    }

    public function saveState() {
        $_SESSION['board'] = $this->board;
        $_SESSION['hand'] = $this->hand;
        $_SESSION['player'] = $this->activePlayer;
        $_SESSION['previousMove'] = $this->previousMove;
        $_SESSION['error'] = $this->error;
        $_SESSION['gameId'] = $this->gameId;
    }

    public function constructBoard() {
        $min_x = 1000;
        $min_y = 1000;
        foreach ($this->board as $pos => $tile) {
            list($x, $y) = $this->expolodePos($pos);
            if ($x < $min_x) $min_x = $x;
            if ($y < $min_y) $min_y = $y;
        }
        $board = "";
        foreach (array_filter($this->board) as $pos => $tile) {
            list($x, $y) = $this->expolodePos($pos);
            $h = count($tile);
            $player = $tile[$h-1][0];
            $stackedTiles = $h > 1 ? ' stackedTiles' : '';
            $left = ($x - $min_x) * 4 + ($y - $min_y) * 2;
            $top = ($y - $min_y) * 4;
            $value = $tile[$h-1][1];
    
            $board .= "<div class=\"tile player$player$stackedTiles\" style=\"left: {$left}em; top: {$top}em;\">($x,$y)<span>$value</span></div>";
        }

        return $board;
    }

    public function availableMoves() {
        $to = [];
        foreach ($GLOBALS['OFFSETS'] as $pq) {
            foreach (array_keys($this->board) as $pos) {
                $pq2 = explode(',', $pos);
                $to[] = ($pq[0] + $pq2[0]).','.($pq[1] + $pq2[1]);
            }
        }
        $to = array_unique($to);
        if (!count($to)) $to[] = '0,0';
        return $to;
    }

    public function isMoveAvailable($from, $to) {
        if ($this->hand[$this->getActivePlayer()]['Q']) {
            return [];
        }

        $to = [];
        foreach (array_keys($this->board) as $pos) {
            $tile = $this->board[$pos][count($this->board[$pos]) - 1];
            if ($tile[0] != $this->getActivePlayer()) {
                continue;
            }
            $to[] = $pos;
        }
        return $to;
    }

    public function getAvailablePositions() {
        $to = [];
        $hand = $this->hand($this->getActivePlayer());
        foreach ($GLOBALS['OFFSETS'] as $pq) {
            foreach (array_keys($this->board) as $pos) {
                list($x, $y) = explode(',', $pos);
                $new = ($pq[0] + $x).','.($pq[1] + $y);

                if (isset($this->board[$new])) {
                    continue;
                }
                if (count($this->board) && !hasNeighBour($new, $this->board)) {
                    continue;
                }
                if (array_sum($hand) < 11 && !neighboursAreSameColor($this->getActivePlayer(), $new, $this->board)) {
                    continue;
                }
                $to[] = $new;
            }
        }
        $to = array_unique($to);
        if (!count($to)) $to[] = '0,0';
        return $to;
    }

    private function expolodePos($pos) {
        return explode(',', $pos);
    }

    public function getPlayerName($player) {
        return $player == 0 ? 'White' : 'Black';
    }

    public function getActivePlayer() {
        return $this->activePlayer;
    }

    public function getPreviousMoves() {
        $stmt = $this->database->prepare('SELECT * FROM moves WHERE game_id = ?');
        $stmt->bind_param('i', $this->game_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $html = "";
        while ($row = $result->fetch_array()) {
            $html .= '<li>'.$row[2].' '.$row[3].' '.$row[4].'</li>';
        }
        return $html;
    }

    public function hand($player) {
        return array_filter($this->hand[$player], function ($ct) { return $ct > 0; });
    }

    public function getHand($player) {
        $html = "";
        foreach ($this->hand[$player] as $tile => $ct) {
            for ($i = 0; $i < $ct; $i++) {
                $html .= '<div class="tile player'.$player.'"><span>'.$tile."</span></div> ";
            }
        }
        return $html;    
    }

    public function play($piece, $to) {
        $this->clearError();
        
        $hand = $this->hand($this->getActivePlayer());
        
        if (!$hand[$piece]) {
            $this->setError("Player does not have tile");
        } elseif (isset($this->board[$to])) {
            $this->setError('Board position is not empty');
        } elseif (count($this->board) && !hasNeighBour($to, $this->board)) {
            $this->setError("board position has no neighbour");
        } elseif (array_sum($hand) < 11 && !neighboursAreSameColor($this->getActivePlayer(), $to, $this->board)) {
            $this->setError("Board position has opposing neighbour");
        } elseif (array_sum($hand) <= 8 && $hand['Q'] && $piece != 'Q') {
            $this->setError("Must play queen bee");
        }
        if ($this->hasError()) return;

        $this->board[$to] = [[$this->getActivePlayer(), $piece]];
        $this->hand[$this->getActivePlayer()][$piece]--;
        $this->activePlayer = $this->getActivePlayer() == 0 ? 1 : 0;
        $stmt = $this->database->prepare('insert into availableMoves (game_id, type, move_from, move_to, previous_id, state) values (?, ?, "play", ?, ?, ?)');
        $stmt->bind_param('issis', $this->game_id, $piece, $to, $this->previousMove, $this->setState());
        $stmt->execute();
        $this->previousMove = $this->database->insert_id;
    }

    public function changeActivePlayer() {
        $this->activePlayer = 1 - $this->activePlayer; 
    }

    public function move($piece, $to) {
        $this->clearError();
        $hand = $this->hand($this->getActivePlayer());

        if (!isset($this->board[$piece])) {
            $this->setError('Board position is empty');
        } elseif ($this->board[$piece][count($this->board[$piece]) - 1][0] != $this->getActivePlayer()) {
            $this->setError("Tile is not owned by player");
        } elseif ($hand['Q']) {
            $this->setError("Queen bee is not played");
        }
        if ($this->hasError()) return;

        $tile = array_pop($this->board[$piece]);
        if (!hasNeighBour($to, $this->board)) {
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
                    if (!slide($this->board, $piece, $to)) {
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

            if (count($this->board[$piece]) == 0) {
                unset($this->board[$piece]);
            }

            $this->changeActivePlayer();
            $stmt = $this->database->prepare('insert into availableMoves (game_id, type, move_from, move_to, previous_id, state) values (?, "move", ?, ?, ?, ?)');
            $stmt->bind_param('issis', $this->game_id, $piece, $to, $this->previousMove, $this->setState());
            $stmt->execute();
            $this->previousMove = $this->database->insert_id;
        }
    }

    public function pass() {
        $stmt = $this->database->prepare('insert into availableMoves (game_id, type, move_from, move_to, previous_id, state) values (?, "pass", null, null, ?, ?)');
        $stmt->bind_param('iis', $this->game_id, $this->previousMove, $this->setState());
        $stmt->execute();
        $this->previousMove = $this->database->insert_id;
        $this->changeActivePlayer();
    }

    public function undo() {
        $stmt = $this->database->prepare('SELECT * FROM availableMoves WHERE id = ?');
        $stmt->bind_param('i', $this->previousMove);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_array();
        $this->previousMove = $result[5];
        $this->getState($result[6]);
    }

    public function restart() {
        $this->board = [];
        $this->hand = [0 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3], 1 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3]];
        $this->activePlayer = 0;
        $this->previousMove = 0;
        $stmt = $this->database->prepare('INSERT INTO games VALUES ()');
        $stmt->execute();
        $this->game_id = $this->database->insert_id;
    }
}