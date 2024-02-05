<?php

namespace src\Controllers;

include 'models/database.php';

trait GameState {
    // Load game state from session
    public function loadSession() {
        // Check if board is set in the session
        if(!isset($_SESSION['board'])) {
            // If not, throw an exception
            throw new \Exception('Not able to get state');
        }
        // Load game state from session variables
        $this->game_id = $_SESSION['game_id'];
        $this->board = $_SESSION['board'];
        $this->hand = $_SESSION['hand'];
        $this->error = $_SESSION['error'];
        $this->activePlayer = $_SESSION['player'];
        $this->lastMove = $_SESSION['lastMove'];
    }

    // Set game state as a serialized string
    public function setState() {
        // Return a serialized string of the game state
        return serialize([$this->board, $this->hand, $this->activePlayer]);
    }

    // Get game state from a serialized string
    public function getState($state) {
        // Unserialize the string and assign the values to the corresponding properties
        list($this->board, $this->hand, $this->activePlayer) = unserialize($state);
    }

    // Save game state to session
    public function saveStateToSession() {
        // Save the game state to session variables
        $_SESSION['game_id'] = $this->game_id; 
        $_SESSION['board'] = $this->board; 
        $_SESSION['hand'] = $this->hand; 
        $_SESSION['error'] = $this->error;
        $_SESSION['player'] = $this->activePlayer;
        $_SESSION['lastMove'] = $this->lastMove;
    }
}

