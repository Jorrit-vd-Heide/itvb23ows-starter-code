<?php

include_once '/var/www/html/Models/hiveGameModel.php';

class HiveGameView {
    private $game;

    // Define the constructor to inject the game object
    public function __construct(HiveGameModel $game) {
        // Assign the injected game object to the property
        $this->game = $game;
    }

    // Define a public method to get the HTML string representing the game board
    public function getBoardHtml() {
        // Get the board array from the game object
        $board = $this->game->board;

        // Find the minimum x and y positions on the board
        $minPosition = $this->findMinimumPosition($board);

        // Initialize the board HTML string
        $boardHtml = "" ;

        // Loop through each tile on the board
        foreach (array_filter($board) as $pos => $tile) {
            // Parse the position of the current tile
            list($x, $y) = $this->parsePos($pos);

            // Calculate the position of the current tile relative to the minimum position
            $position = $this->calculateTilePosition($x, $y, $minPosition);

            // Get the player number and value of the current tile
            $player = $tile[count($tile) - 1][0];
            $value = $tile[count($tile) - 1][1];

            // Create an HTML div element for the current tile
            $boardHtml .= "<div class=\"tile player{$player}\" style=\"left: {$position['left']}em; top: {$position['top']}em;\">($x,$y)<span>{$value}</span></div>";
        }

        // Return the HTML string representing the game board
        return $boardHtml;
    }

    // Define a private method to find the minimum x and y positions on the board
    private function findMinimumPosition($board) {
        // Initialize the minimum x and y positions to the maximum integer value
        $min_x = PHP_INT_MAX;
        $min_y = PHP_INT_MAX;

        // Loop through each tile on the board
        foreach ($board as $pos => $tile) {
            // Parse the position of the current tile
            list($x, $y) = $this->parsePos($pos);

            // Find the minimum x position
            $min_x = min($min_x, $x);

            // Find the minimum y position
            $min_y = min($min_y, $y);
        }

        // Return the minimum x and y positions
        return ['x' => $min_x, 'y' => $min_y];
    }

    // Define a private method to calculate the position of a tile relative to the minimum position
    private function calculateTilePosition($x, $y, $minPosition) {
        // Calculate the left position
        $left = ($x - $minPosition['x']) * 4 + ($y - $minPosition['y']) * 2;

        // Calculate the top position
        $top = ($y - $minPosition['y']) * 4;

        // Return the calculated position
        return ['left' => $left, 'top' => $top];
    }

    // Define a private method to parse a position string into an array of x and y values
    private function parsePos($pos) {
        // Split the position string into an array using the comma as the delimiter
        return explode(',', $pos);
    }

    // Define a public method to get the HTML string representing the player's hand
    public function getHandHtml($player) {
        $html = "";
        $hand = $this->game->hand;
        foreach ($hand[$player] as $tile => $ct) {
            for ($i = 0; $i < $ct; $i++) {
                $html .= '<div class="tile player'.$player.'"><span>'.$tile."</span></div> ";
            }
        }
        return $html;    
    }
}
