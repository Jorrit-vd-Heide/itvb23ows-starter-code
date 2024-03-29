<?php

//Include necessary files
include_once '/var/www/html/Models/hiveGameModel.php';
include_once '/var/www/html/Views/hiveGameView.php';
include_once '/var/www/html/Views/util.php';

class HiveGameController {
    // Properties
    private $model;
    private $view;

    // Constructor
    public function __construct($model, $view) {
        $this->model = $model;
        $this->view = $view;
    }
    
    // Check if there's an error
    public function hasError() {
        return $this->model->error !== null;
    }

    // Get the error message
    public function getError() {
        return $this->model->error;
    }

    // Set the error message
    public function setError($error) {
        $this->model->error = $error;
    }

    // Clear the error messag
    public function clearError() {
        $this->model->error = null;
    }

    // Get the current hand of a player
    public function getHand($player) {
        return array_filter($this->model->hand[$player], function ($ct) { return $ct > 0; });
    }

    // Set the hand of a player
    public function setHand($player, $hand) {
        $this->model->hand[$player] = $hand;
    }

    // Get the name of a player
    public function getPlayerName($player) {
        return $player == 0 ? 'White' : 'Black';
    }

    // Get the active player
    public function getActivePlayer() {
        return $this->model->activePlayer;
    }

    public function changePlayer() {
        $this->model->activePlayer = 1 - $this->model->activePlayer; 
    }

    // Get possible moves for the game
    public function getPossibleMoves() {
        $possibleMoves = [];
    
        // Iterate over each piece on the board
        foreach (array_keys($this->model->board) as $piecePosition) {
            $pieceCoordinates = explode(',', $piecePosition);
    
            // Calculate possible moves for each piece
            $moves = $this->calculateMovesForPiece($pieceCoordinates);
    
            // Merge moves into the list of possible moves
            $possibleMoves = array_merge($possibleMoves, $moves);
        }
    
        // Remove duplicates and return possible moves
        $possibleMoves = array_unique($possibleMoves);
        if (empty($possibleMoves)) {
            $possibleMoves[] = '0,0';
        }
        
        return $possibleMoves;
    }
    
    // Calculate possible moves for a single piece
    private function calculateMovesForPiece($pieceCoordinates) {
        $moves = [];
    
        // Iterate over predefined offsets for each piece
        foreach ($GLOBALS['OFFSETS'] as $offset) {
            // Calculate new position based on offset
            $newPositionX = $offset[0] + $pieceCoordinates[0];
            $newPositionY = $offset[1] + $pieceCoordinates[1];
    
            // Add the new position to the list of possible moves
            $moves[] = "$newPositionX,$newPositionY";
        }
    
        return $moves;
    }
    

    // Get possible plays for the active player
    public function getPossiblePlays() {
        $possiblePlays = [];
        $activePlayer = $this->getActivePlayer();
        $hand = $this->getHand($activePlayer);
    
        // Iterate over each offset
        foreach ($GLOBALS['OFFSETS'] as $offset) {
            // Iterate over each position on the board
            foreach (array_keys($this->model->board) as $position) {
                list($x, $y) = explode(',', $position);
                $newX = $offset[0] + $x;
                $newY = $offset[1] + $y;
                $newPos = "$newX,$newY";
    
                // Check if the position is occupied
                if (isset($this->model->board[$newPos])) {
                    continue;
                }
    
                // Check if the position has neighbors
                if (count($this->model->board) && !hasNeighbour($newPos, $this->model->board)) {
                    continue;
                }
    
                // Check if neighbors are of the same color
                if (array_sum($hand) < 11 && !checkIfNeigbourIsSameColor($activePlayer, $newPos, $this->model->board)) {
                    continue;
                }
    
                // Add the position to possible plays
                $possiblePlays[] = $newPos;
            }
        }
    
        // Remove duplicates and handle empty plays
        $possiblePlays = array_unique($possiblePlays);
        if (empty($possiblePlays)) {
            $possiblePlays[] = '0,0';
        }
        
        return $possiblePlays;
    }

    // Common error checking method
    private function checkTilePlacementErrors($hand, $piece, $to) {
        if (!$hand[$piece]) {
            $this->setError("Player does not have tile");
        } elseif (isset($this->model->board[$to])) {
            $this->setError('Board position is not empty');
        } elseif (count($this->model->board) && !hasNeighBour($to, $this->model->board)) {
            $this->setError("Board position has no neighbour");
        } elseif (array_sum($hand) < 11 && !checkIfNeigbourIsSameColor($this->getActivePlayer(), $to, $this->model->board)) {
            $this->setError("Board position has opposing neighbour");
        } elseif (array_sum($hand) <= 8 && isset($hand['Q']) && $hand['Q'] > 0 && $piece != 'Q') {
            $this->setError("Must play queen bee");
        }
    }

    // Play a tile on the board
    public function playTile($piece, $to) {
        $this->clearError();
        $hand = $this->getHand($this->getActivePlayer());
        
        // Check common errors
        $this->checkTilePlacementErrors($hand, $piece, $to);
        if ($this->hasError()) return;

        $this->model->board[$to] = [[$this->getActivePlayer(), $piece]];
        $this->model->hand[$this->getActivePlayer()][$piece]--;
        $this->model->activePlayer = $this->getActivePlayer() == 0 ? 1 : 0;
        $stmt = $this->model->database->prepare('insert into moves (game_id, type, move_from, move_to, previous_id, state) values (?, ?, "play", ?, ?, ?)');
        $setState = $this->model->setState();
        $stmt->bind_param('issis', $this->model->game_id, $piece, $to, $this->model->last_move, $setState);
        $stmt->execute();
        $this->model->last_move = $this->model->database->insert_id;

        // Check for winner after playing a tile
        $winner = $this->determineWinner();
        if ($winner) {
            return $winner; // Return the winner if the game is won
        }

        // If no winner, return null
        return null;
    }

    // Get movable tiles
    public function getTilesToMove() {
        $activePlayer = $this->getActivePlayer();
        $tilesToMove = [];
    
        // Check if the player has the 'Q' tile
        if ($this->model->hand[$activePlayer]['Q']) {
            return $tilesToMove; // No tiles can be moved
        }
    
        // Iterate over positions on the board
        foreach (array_keys($this->model->board) as $position) {
            $topTile = end($this->model->board[$position]); // Get the top tile
            if ($topTile[0] === $activePlayer) {
                $tilesToMove[] = $position; // Add position to tiles to move
            }
        }
    
        return $tilesToMove;
    }

    // Move a tile on the board
    public function moveTile($piece, $to) {
        $this->clearError();
        $hand = $this->getHand($this->getActivePlayer());
    
        $this->validateTilePlacement($piece, $to, $hand);
        
        if ($this->hasError()) return;
    
        $tile = array_pop($this->model->board[$piece]);
        $this->validateMove($piece, $to, $tile);
    
        if ($this->hasError()) {
            $this->restoreOriginalPosition($piece, $tile);
        } else {
            $this->placeTileOnBoard($to, $tile);
            $this->removeOriginalPosition($piece);
            $this->changePlayer();
            $this->recordMove($piece, $to);
        }

        // Check for winner after moving a tile
        $winner = $this->determineWinner();
        if ($winner) {
            return $winner; // Return the winner if the game is won
        }

        // If no winner, return null
        return null;
    }

    // Check if the board position is empty
    private function validateTilePlacement($piece, $to, $hand) {
        if (!isset($this->model->board[$piece])) {
            $this->setError('Board position is empty');
        } 
        // Check if the board position has no neighbor
        elseif (count($this->model->board) && !hasNeighBour($to, $this->model->board)) {
            $this->setError("Board position has no neighbour");
        } 
        // Check if the board position has opposing neighbor
        elseif (array_sum($hand) < 11 && !checkIfNeigbourIsSameColor($this->getActivePlayer(), $to, $this->model->board)) {
            $this->setError("Board position has opposing neighbour");    
        } 
        // Check if the player owns the tile
        elseif ($this->model->board[$piece][count($this->model->board[$piece]) - 1][0] != $this->getActivePlayer()) {
            $this->setError("You do not own this tile");
        } 
        // Check if the queen bee is not played
        elseif (isset($hand['Q'])) {
            $this->setError("Queen bee is not played");
        }
    }

    // Check if the move would split the hive
    private function validateMove($piece, $to, $tile) {
        if (!hasNeighBour($to, $this->model->board)) {
            $this->setError("Move would split hive");
        } else {
            // Get all positions on the board
            $all = array_keys($this->model->board);
            // Calculate reachable tiles
            $reachableTiles = $this->calculateReachableTiles($all);
    
            // Check if the move is invalid
            if ($this->isInvalidMove($piece, $to, $tile, $reachableTiles)) {
                $this->setError("Invalid move");
            }
        }
    }

    // Initialize reachable tiles array
    private function calculateReachableTiles($hive) {
        $reachableTiles = [];
        // Initialize queue with a hive position
        $queue = [array_shift($hive)];
    
        // Traverse the hive using BFS
        while ($queue) {
            // Get the next position from the queue
            $next = explode(',', array_shift($queue));
            // Iterate over neighbor offsets
            foreach ($GLOBALS['OFFSETS'] as $pq) {
                list($p, $q) = $pq;
                $p += $next[0];
                $q += $next[1];
                // Check if the neighbor position is in the hive
                if (in_array("$p,$q", $hive)) {
                    // Add the neighbor position to the queue and reachable tiles
                    $queue[] = "$p,$q";
                    $hive = array_diff($hive, ["$p,$q"]);
                    $reachableTiles[] = "$p,$q";
                }
            }
        }
    
        // Return reachable tiles
        return $reachableTiles;
    }

    // Check different conditions for invalid moves and set error messages
    private function isInvalidMove($piece, $to, $tile, $reachableTiles) {
        if ($piece == $to) {
            $this->setError('Tile must move');
        } elseif (isset($this->model->board[$to]) && $tile[1] != "B") {
            $this->setError('Tile not empty');
        } elseif ($tile[1] == "Q" || $tile[1] == "B") {
            if (!canSlide($this->model->board, $piece, $to, $tile[1] == "B")) {
                $this->setError('Tile must slide');
            }
        } elseif ($tile[1] == "G") {
            if (isNeighbour($piece, $to)) {
                $this->setError('Jump must be larger than 1');
            } elseif (checkIfPathContainsEmptyTiles($piece, $to, $this->model->board)) {
                $this->setError('Path can not contain empty tiles');
            }
        } elseif ($tile[1] == "A") {
            if (!availableSlidePath($this->model->board, $piece, $to, false)){
                $this->setError('Tile has to perform slide move');
            } 
        } elseif ($tile[1] == "S") {
            if (!availableSlidePath($this->model->board, $piece, $to, true)) {
                $this->setError('Tile must slide exactly 3 tiles');
            }
        }
    
        return false; // Move is valid
    }

    // Check if the original position exists on the board
    private function restoreOriginalPosition($piece, $tile) {
        if (isset($this->model->board[$piece])) {
            // Push the tile back to the original position
            array_push($this->model->board[$piece], $tile);
        } else {
            // Set the original position with the tile
            $this->model->board[$piece] = [$tile];
        }
    }

    // Check if the destination position exists on the board
    private function placeTileOnBoard($to, $tile) {
        if (isset($this->model->board[$to])) {
            // Push the tile to the destination position
            array_push($this->model->board[$to], $tile);
        } else {
            // Set the destination position with the tile
            $this->model->board[$to] = [$tile];
        }
    }

    // Check if the original position has no tiles
    private function removeOriginalPosition($piece) {
        if (count($this->model->board[$piece]) == 0){
            // Remove the original position from the board
            unset($this->model->board[$piece]);
        }
    }

    // Prepare and execute SQL statement to record the move
    private function recordMove($piece, $to) {
        $stmt = $this->model->database->prepare('insert into moves (game_id, type, move_from, move_to, previous_id, state) values (?, "move", ?, ?, ?, ?)');
        $setState = $this->model->setState();
        $stmt->bind_param('issis', $this->model->game_id, $piece, $to, $this->model->last_move, $setState);
        $stmt->execute();
        $this->model->last_move = $this->model->database->insert_id;
    }
    
    // Pass the turn to the other player
    public function pass() {
        // Get the active player's hand
        $activePlayerHand = $this->getHand($this->getActivePlayer());

        // Check if the active player has plays available
        if (count($activePlayerHand) > 0) {
            $this->setError('Player still has plays available');
            return;
        }

        // Check if moves are possible
        $movableTiles = $this->getTilesToMove();

        foreach ($movableTiles as $tile) {
            if ($this->attemptMove($tile)) {
                $this->setError('Player still has moves available');
                return;
            }
        }

        $insertQuery = 'INSERT INTO moves (game_id, type, move_from, move_to, previous_id, state) VALUES (?, "pass", null, null, ?, ?)';
        $stmt = $this->model->database->prepare($insertQuery);
        $setState = $this->model->setState();
        $stmt->bind_param('iis', $this->model->game_id, $this->model->last_move, $setState);
        $stmt->execute();
        $this->model->last_move = $this->model->database->insert_id;
        $this->changePlayer();
    }

    // Undo the last move
    public function undo() {
        $selectQuery = 'SELECT * FROM moves WHERE id = ? AND game_id = ?';
        $stmt = $this->model->database->prepare($selectQuery);
        $stmt->bind_param('ii', $this->model->last_move, $this->model->game_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        if ($result) {
            $this->model->last_move = $result['previous_id'];
            $this->getState($result['state']);
        }
    }

    // Restart the game
    public function restart() {
        $this->model->board = [];
        $initialHand = ["Q" => 1, "B" => 2, "S" => 2, "A" => 3, "G" => 3];
        $this->model->hand = [0 => $initialHand, 1 => $initialHand];
        $this->model->activePlayer = 0;
        $this->model->last_move = 0;
        $insertQuery = 'INSERT INTO games () VALUES ()';
        $stmt = $this->model->database->prepare($insertQuery);
        $stmt->execute();
        $this->model->game_id = $this->model->database->insert_id;
    }

    // Add hive winner logic
    public function determineWinner() {
        // Check if the white queen bee is surrounded by black tiles
        $whiteQueenPosition = array_search('Q', $this->model->board[0] ?? []);
        if ($whiteQueenPosition !== false && !checkIfNeigbourIsSameColor(0, $whiteQueenPosition, $this->model->board)) {
            return 'Black';
        }

        // Check if the black queen bee is surrounded by white tiles
        $blackQueenPosition = array_search('Q', $this->model->board[1] ?? []);
        if ($blackQueenPosition !== false && !checkIfNeigbourIsSameColor(1, $blackQueenPosition, $this->model->board)) {
            return 'White';
        }

        // Check for tie (stalemate)
        if ($this->isStalemate()) {
            return 'Tie';
        }

        // If none of the above conditions are met, the game is still ongoing
        return null;
    }

    // Check for stalemate
    private function isStalemate() {
        // Check if there are any movable tiles left on the board
        $movableTiles = $this->getTilesToMove();

        // If there are movable tiles left, the game is not in a stalemate
        return empty($movableTiles);
    }

}
