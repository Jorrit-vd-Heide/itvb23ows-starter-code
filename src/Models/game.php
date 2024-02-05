<?php

namespace src\Models;

// Game class representing a single game session
include_once 'controllers/gameStateController.php';
use src\Controllers\GameState;
include_once 'controllers/boardController.php';
use src\Controllers\Board;
include_once 'controllers/playerController.php';
use src\Controllers\Player;
include_once 'controllers/moveHistoryController.php';
use src\Controllers\MoveHistory;
include_once 'controllers/playController.php';
use src\Controllers\PlayTile;
include_once 'controllers/moveController.php';
use src\Controllers\MoveTile;

class Game {
    // GameState, Board, and PlayTile traits are used for game state management, board representation, and play tile functionality
    use GameState, Board, Player, MoveHistory, PlayTile, MoveTile;
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
    // lastMove: integer representing the last move made in the game
    private $lastMove = 0;

    // constructor initializing the game with a database object
    public function __construct($database) {
       $this->database = $database;
    }

    // changePlayer: changes the current player's turn
    public function changePlayer() {
        $this->activePlayer = 1 - $this->activePlayer;
    }

    // pass: records a pass move in the game and changes the current player's turn
    public function pass() {
        // Prepare an SQL statement to insert a new move into the moves table
        $stmt = $this->database->prepare('insert into moves (game_id, type, move_from, move_to, previous_id, state) values (?, "pass", null, null, ?, ?)');

        // Bind the session variables to the prepared statement
        $stmt->bind_param('iis', $this->game_id, $this->lastMove, $this->setState());

        // Execute the prepared statement
        $stmt->execute();

        // Get the ID of the newly inserted move
        $_SESSION['lastMove'] = $this->database->insert_id;

        // Change the current player's turn
        $_SESSION['player'] = 1 - $_SESSION['player'];
    }

    // undo: undoes the last move made in the game
    public function undo() {
        // Prepare an SQL statement to select the last move from the moves table
        $stmt = $this->database->prepare('SELECT * FROM moves WHERE id = '.$_SESSION['lastMove']);

        $stmt->bind_param('ii', $this->lastMove, $this->game_id);

        // Execute the prepared statement
        $stmt->execute();

        // Fetch the result of the query
        $result = $stmt->get_result()->fetch_array();

        // Update the lastMove variable with the state of the previous move
        $this->lastMove = $result[5];

        // Get the state of the previous move
        $this->getState($result[6]);
    }

    // restart: restarts the game by resetting the board, hand, and current player's turn
    public function restart() {
        // Reset the board and hand variables
        $this->board = [];
        $this->hand = [
            0 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3],
            1 => ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3]
        ];

        // Reset the current player's turn
        $this->activePlayer = 0;
        $this->lastMove = 0;
        // Prepare an SQL statement to insert a new game into the agames table
        $stmt = $this->database->prepare('INSERT INTO games VALUES ()');

        // Execute the prepared statement
        $stmt->execute();

        // Get the ID of the newly inserted game
        $this->game_id = $this->database->insert_id;
    }
}