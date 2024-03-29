<?php

// An array of offsets used to check neighboring tiles in a 2D grid
$GLOBALS['OFFSETS'] = [[0, 1], [0, -1], [1, 0], [-1, 0], [-1, 1], [1, -1]];

/**
* Checks if two tiles are neighbors in a 2D grid
*
* @param string $a A tile represented as a string with format "x,y"
* @param string $b Another tile represented as a string with format "x,y"
* @return bool True if the tiles are neighbors, false otherwise
*/
function isNeighbour($a, $b)
{
    $a = explode(',', $a);
    $b = explode(',', $b);

    foreach ($GLOBALS['OFFSETS'] as $pq) {
        $p = $a[0] + $pq[0];
        $q = $a[1] + $pq[1];
        if ($p == $b[0] && $q == $b[1]) {
            return true;
        }

    }

    return false;
}

/**
* Checks if a tile has a neighboring tile with the same player's piece on the board
*
* @param string $a A tile represented as a string with format "x,y"
* @param array $board A 2D grid representing the game board
* @return bool True if there is a neighboring tile with the same player's piece, false otherwise
*/
function hasNeighBour($a, $board)
{
    foreach (array_keys($board) as $b) {
        if (isNeighbour($a, $b)) {
            return true;
        }

    }
}

/**
 * Checks if a path from one tile to another contains empty tiles
 *
 * @param string $from A tile represented as a string with format "x,y"
 * @param string $to Another tile represented as a string with format "x,y"
 * @param array $board A 2D grid representing the game board
 * @return bool True if the path contains empty tiles, false otherwise
 */
function checkIfPathContainsEmptyTiles($from, $to, $board)
{
    // Convert tile coordinates to arrays
    $from = explode(',', $from);
    $to = explode(',', $to);

    // Determine the direction of movement
    $dx = $from[0] <=> $to[0];
    $dy = $from[1] <=> $to[1];

    // Iterate over the tiles along the path
    for ($x = $from[0], $y = $from[1]; $x != $to[0] || $y != $to[1]; $x += $dx, $y += $dy) {
        // Check if the tile is empty
        if (!isset($board["$x,$y"])) {
            return true; // Path contains empty tiles
        }
    }

    return false; // Path does not contain empty tiles
}


/**
 * Checks if all neighboring tiles of a given tile have the same player's piece on the board
 *
 * @param string $player The player's identifier
 * @param string $tile A tile represented as a string with format "x,y"
 * @param array $board A 2D grid representing the game board
 * @return bool True if all neighboring tiles have the same player's piece, false otherwise
 */
function checkIfNeigbourIsSameColor($player, $tile, $board)
{
    foreach ($board as $position => $stack) {
        // Skip empty positions
        if (empty($stack)) {
            continue;
        }
    
        // Get the color of the top tile in the stack
        $topTileColor = $stack[count($stack) - 1][0];
    
        // Check if the top tile belongs to the opponent and if it's a neighbor of the given position
        if ($topTileColor != $player && isNeighbour($tile, $position)) {
            return false;
        }
    }
    
    // If no opposing neighbor found, return true
    return true;
}


/**
* Returns the length of a given tile
*
* @param array|null $tile A tile represented as an array with format [x, y]
* @return int The length of the tile, or 0 if the tile is null
*/
function len($tile)
{
    return $tile ? count($tile) : 0;
}

/**
 * Checks if a tile can slide to another tile under specific conditions
 *
 * @param array $board A 2D grid representing the game board
 * @param string $from A tile represented as a string with format "x,y"
 * @param string $to Another tile represented as a string with format "x,y"
 * @param bool $isBeetle Optional: Whether the sliding piece is a beetle
 * @param bool $isSpider Optional: Whether the sliding piece is a spider
 * @return bool True if the tile can slide to the target tile, false otherwise
 */
function canSlide($board, $from, $to, $isBeetle = false, $isSpider = false)
{
    // If it's a beetle, it can slide anywhere
    if ($isBeetle) {
        return true;
    }

    // If it's a spider, it can slide if the target tile is adjacent
    if ($isSpider) {
        return isNeighbour($from, $to);
    }

    // For other pieces, they can slide if the target tile is adjacent and empty
    return isNeighbour($from, $to) && isEmpty($board, $to);
}


/**
 * Get common neighbors between two tiles
 *
 * @param array $board A 2D grid representing the game board
 * @param string $tileA A tile represented as a string with format "x,y"
 * @param string $tileB Another tile represented as a string with format "x,y"
 * @return array Common neighbors between the two tiles
 */
function getCommonNeighbors($board, $tileA, $tileB)
{
    $neighborsA = getNeighbors($board, $tileA);
    $neighborsB = getNeighbors($board, $tileB);

    // Get the common neighbors between tileA and tileB
    return array_intersect($neighborsA, $neighborsB);
}

/**
 * Checks if there is a slide path between two tiles.
 *
 * @param array $board A 2D grid representing the game board
 * @param string $from A tile represented as a string with format "x,y"
 * @param string $to Another tile represented as a string with format "x,y"
 * @param bool $isSpider Optional: Whether the sliding piece is a spider
 * @return bool True if a slide path exists between the two tiles, false otherwise
 */
function availableSlidePath($board, $from, $to, $isSpider = false)
{
    // Helper function to recursively explore the board
    function explorePath($board, $current, $target, &$visited, &$paths, $isSpider)
    {
        // Base case: If the current tile is the target tile and spider condition met, return true
        if ($current == $target && (!$isSpider || count($paths[$current][0]) == 4)) {
            return true;
        }

        // Mark the current tile as visited
        $visited[$current] = true;

        // Explore each neighbor of the current tile
        foreach (getNeighbours($current) as $neighbour) {
            // Check if the neighbor is a valid move and has not been visited
            if (isValidMove($board, $current, $neighbour) && !isset($visited[$neighbour])) {
                // Record the path to the neighbor
                $paths[$neighbour] = array_map(function ($path) use ($neighbour) {
                    return [...$path, $neighbour];
                }, $paths[$current] ?? [[$current]]);

                // Recursively explore the neighbor
                if (explorePath($board, $neighbour, $target, $visited, $paths, $isSpider)) {
                    return true; // If a path is found, return true
                }
            }
        }

        return false; // If no path is found, return false
    }

    // Initialize arrays to track visited tiles and paths
    $visited = [];
    $paths = [$from => [[$from]]];

    // Start exploring from the initial tile
    return explorePath($board, $from, $to, $visited, $paths, $isSpider);
}

/**
 * Checks if a move from one tile to another is valid.
 *
 * @param array $board A 2D grid representing the game board
 * @param string $from A tile represented as a string with format "x,y"
 * @param string $to Another tile represented as a string with format "x,y"
 * @return bool True if the move is valid, false otherwise
 */
function isValidMove($board, $from, $to)
{
    // Check if the target tile is empty and adjacent to the source tile
    return !isset($board[$to]) && isAdjacent($from, $to);
}

/**
 * Checks if two tiles are adjacent.
 *
 * @param string $tile1 A tile represented as a string with format "x,y"
 * @param string $tile2 Another tile represented as a string with format "x,y"
 * @return bool True if the tiles are adjacent, false otherwise
 */
function isAdjacent($tile1, $tile2)
{
    [$x1, $y1] = explode(',', $tile1);
    [$x2, $y2] = explode(',', $tile2);
    $dx = abs($x1 - $x2);
    $dy = abs($y1 - $y2);
    return ($dx + $dy == 1) || ($dx == 1 && $dy == 1);
}

/**
 * Gets neighboring tiles of a given tile.
 *
 * @param string $tile A tile represented as a string with format "x,y"
 * @return array An array of neighboring tiles
 */
function getNeighbours($tile)
{
    [$x, $y] = explode(',', $tile);
    return [
        ($x - 1) . ",$y",
        ($x + 1) . ",$y",
        "$x," . ($y - 1),
        "$x," . ($y + 1)
    ];
}
