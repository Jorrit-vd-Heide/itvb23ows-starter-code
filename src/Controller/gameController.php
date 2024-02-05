<?php

require_once 'gameStateController.php';

class Game{
    use GameState;

    private $game_id = 0;
    public $board = array();
    private $hand = array();
    private $error = null;
    public $current_player = 0;
    private $database;
    private $last_move = 0;

    public function __construct($database) {
        $this->database = $database;
    }
}