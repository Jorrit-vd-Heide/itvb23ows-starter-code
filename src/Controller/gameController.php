<?php

// Game class representing a single game session

require_once 'gameController.php';require_once 'Controller.php';require_once 'Controller.php';
class Game {
   // GameState, Board, and PlayTile traits are used for game state management, board representation, and play tile functionality

   use GameState, Board, PlayTile;

   // game_id: unique identifier for each game
   private $game_id = 0;
   // board: 2D array representing the game board
   public $board = array();
   // hand: array to store tiles in a player's hand
   private $hand = array();
   // error: variable to store any error messages during gameplay
   private $error = null;
   // current_player: integer representing the current player's turn
   public $current_player = 0;
   // database: instance of the database class for database operations
   private $database;
   // last_move: integer representing the last move made in the game
   private $last_move = 0;

   // constructor initializing the game with a database object
   public function __construct($database) {
       $this->database = $database;
   }
}