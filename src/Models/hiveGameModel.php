<?php

class HiveGameModel {
    public $game_id = 0;
    public $board = array();
    public $hand = array();
    public $error = null;
    public $activePlayer = 0;
    public $database;
    public $last_move = 0;

    public function __construct($database) {
        $this->database = $database;
        // Initialize other properties as needed
    }

    public function loadSession() {
        if (!isset($_SESSION['board'])) {
            throw new Exception('no state available');
        }
        $this->board = $_SESSION['board'];
        $this->activePlayer = $_SESSION['player'];
        $this->hand = $_SESSION['hand'];
        $this->error = $_SESSION['error'];
        $this->game_id = $_SESSION['game_id'];
         // Initialize last_move and move_nr with default values to avoid undefined array key warnings
        $last_move = isset($_SESSION['last_move']) ? $_SESSION['last_move'] : 0;
        $this->last_move = $last_move;
    }

    public function setState() {
        return serialize([$this->hand, $this->board, $this->activePlayer]);
    }

    public function getState($state) {
        list($this->hand, $this->board, $this->activePlayer) = unserialize($state);
    }

    public function saveState() {
        $_SESSION['board'] = $this->board;
        $_SESSION['player'] = $this->activePlayer;
        $_SESSION['hand'] = $this->hand;
        $_SESSION['error'] = $this->error;
        $_SESSION['game_id'] = $this->game_id;
        $_SESSION['last_move'] = $this->last_move;
    }
}
