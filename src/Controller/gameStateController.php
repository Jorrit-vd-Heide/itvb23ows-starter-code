<?php

require_once 'gameController.php';

trait GameState {
    public function loadSession(){
        if(!isset($_SESSION['board'])){
            throw new excepton('Not able to get state');
        }
        $this-> game_id = $_SESSION['game_id'];
        $this-> board = $_SESSION['board'];
        $this-> hand = $_SESSION['hand'];
        $this-> current_player = $_SESSION['player'];
        $this-> last_move = $_SESSION['last_move'];
    }

    public function setState(){
        return serialize([$this-> board, $this-> hand, $this-> current_player]);
    }

    public function getState($state){
        list($this-> board, $this-> hand, $this-> current_player) = unserialize($state);
    }

    public function saveStateToSession(){
        $_SESSION['game_id'] = $this->game_id; 
        $_SESSION['board'] = $this->board; 
        $_SESSION['hand'] = $this->hand; 
        $_SESSION['error'] = $this->error;
        $_SESSION['player'] = $this->current_player;
        $_SESSION['last_move'] = $this->last_move;
    }
}

