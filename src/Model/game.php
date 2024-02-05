<?php

// Game class representing a single game session
require_once 'gameController.php';
require_once 'Controller.php';
require_once 'playerController.php';
require_once 'moveHistoryController.php';
require_once 'playController.php';
require_once 'moveController.php';

class Game {
   // GameState, Board, and PlayTile traits are used for game state management, board representation, and play tile functionality
   use GameState, Board,  Player, MoveHistory, PlayTile, MoveTile;
   // game_id: unique identifier for each game
   private $game_id = 0;
   // board: 2D array representing the game board
   public $board = array();
   // hand: array to store tiles in a player's hand
   private $hand = array();
   // error: variable to store any error messages during gameplay
   private $error = null;
   // activePlayer: integer representing the current player's turn
   public $activePlayer = 0;
   // database: instance of the database class for database operations
   private $database;
   // last_move: integer representing the last move made in the game
   private $last_move = 0;

   // constructor initializing the game with a database object
   public function __construct($database) {
       $this->database = $database;
   }

   public function changePlayer(){
       $this->activePlayer = 1 - $this->activePlayer;
   }

   public function pass(){
        $stmt = $db->prepare('insert into moves
        (game_id, type, move_from, move_to, previous_id, state)
        values
        (?, "pass", null, null, ?, ?)');
        $stmt->bind_param('iis', $_SESSION['game_id'], $_SESSION['last_move'], setState());
        $stmt->execute();
        $_SESSION['last_move'] = $db->insert_id;
        $_SESSION['player'] = 1 - $_SESSION['player'];
   }

   public function undo(){
        $stmt = $db->prepare('SELECT * FROM moves WHERE id = '.$_SESSION['last_move']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_array();
        $this->last_move = $result[5];
        getState($result[6]);
   }

   public function restart(){
        $this->board = [];
        $this->hand = [
            0 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3],
            1 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3]];
        $this->activePlayer = 0;
        $stmt = $this->database->prepare('INSERT INTO games VALUES ()');
        $stmt->execute();
        $this->game_id = $db->insert_id;
   }
}