<?php

namespace src\Controllers;

trait Board {
    // Returns an HTML string representing the game board
    public function getBoardHtml() {
        $minPosition = $this->findMinimumPosition(); // Find the minimum x and y positions on the board
        $boardHtml = "";

        // Loop through each tile on the board
        foreach (array_filter($this->board) as $pos => $tile) {
            list($x, $y) = $this->parsePos($pos); // Parse the position of the current tile
            $position = $this->calculateTilePosition($x, $y, $minPosition); // Calculate the position of the current tile relative to the minimum position
            $player = $tile[count($tile) - 1][0]; // Get the player number of the current tile
            $value = $tile[count($tile) - 1][1]; // Get the value of the current tile

            $boardHtml .= "<div class=\"tile player{$player}\" style=\"left: {$position['left']}em; top: {$position['top']}em;\">($x,$y)<span>{$value}</span></div>"; // Create an HTML div element for the current tile
        }

        return $boardHtml; // Return the HTML string representing the game board
    }

    // Find the minimum x and y positions on the board
    private function findMinimumPosition() {
        $min_x = PHP_INT_MAX;
        $min_y = PHP_INT_MAX;

        // Loop through each tile on the board
        foreach ($this->board as $pos => $tile) {
            list($x, $y) = $this->parsePos($pos);
            $min_x = min($min_x, $x); // Find the minimum x position
            $min_y = min($min_y, $y); // Find the minimum y position
        }

        return ['x' => $min_x, 'y' => $min_y]; // Return the minimum x and y positions
    }

    // Calculate the position of a tile relative to the minimum position
    private function calculateTilePosition($x, $y, $minPosition) {
        $left = ($x - $minPosition['x']) * 4 + ($y - $minPosition['y']) * 2; // Calculate the left position
        $top = ($y - $minPosition['y']) * 4; // Calculate the top position

        return ['left' => $left, 'top' => $top]; // Return the calculated position
    }

    // Returns an array of possible moves for the current player
    public function getPossibleMoves() {
        $to = []; // Initialize an empty array to store possible moves

        // Loop through each possible offset
        foreach ($GLOBALS['OFFSETS'] as $pq) {
            // Loop through each position on the board
            foreach (array_keys($this->board) as $pos) {
                $pq2 = explode(',', $pos); // Parse the position of the current tile
                $to[] = ($pq[0] + $pq2[0]).','.($pq[1] + $pq2[1]); // Calculate a possible move and add it to the array
            }
        }

        $to = array_unique($to); // Remove duplicate possible moves
        if (!count($to)) $to[] = '0,0'; // If there are no possible moves, add a default move

        return $to; // Return the array of possible moves
    }
}