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
    $from = explode(',', $from);
    $to = explode(',', $to);

    $dir = [$from[0] > $to[0] ? -1 : ($from[0] < $to[0] ? 1 : 0),
            $from[1] > $to[1] ? -1 : ($from[1] < $to[1] ? 1 : 0)];

    while ($from[0] != $to[0] || $from[1] != $to[1]) {
        if (!isset($board[$from[0] . "," . $from[1]])) {
            return true;
        }
        $from[0] += $dir[0];
        $from[1] += $dir[1];
    }
    return false;
}

/**
* Checks if all neighboring tiles of a given tile have the same player's piece on the board
*
* @param string $player The player's identifier
* @param string $a A tile represented as a string with format "x,y"
* @param array $board A 2D grid representing the game board
* @return bool True if all neighboring tiles have the same player's piece, false otherwise
*/
function neighboursAreSameColor($player, $a, $board)
{
    foreach ($board as $b => $st) {
        if (!$st) {
            continue;
        }

        $c = $st[count($st) - 1][0];
        if ($c != $player && isNeighbour($a, $b)) {
            return false;
        }

    }
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
    if (!hasNeighBour($to, $board)) {
        return false;
    }

    if ($isBeetle) {
        return true;
    }

    if (!isNeighbour($from, $to)) {
        return false;
    }

    $b = explode(',', $to);
    $common = [];
    foreach ($GLOBALS['OFFSETS'] as $pq) {
        $p = $b[0] + $pq[0];
        $q = $b[1] + $pq[1];
        if (isNeighbour($from, $p . "," . $q) && isset($board[$p . "," . $q])) {
            $common[] = $p . "," . $q;
        }
    }

    if ($isSpider) {
        return count($common) > 0;
    }
    
    return count($common) == 1;
}

/**
 * Checks if there is a slide path between two tiles.
 *
 * @param array $board A 2D grid representing the game board
 * @param string $from A tile represented as a string with format "x,y"
 * @param string $to Another tile represented as a string with format "x,y"
 *
 * @return bool True if a slide path exists between the two tiles, false otherwise
 */
function availableSlidePath($board, $from, $to)
{
    $visited = [];
    $queue = [$from];
    
    while (!empty($queue)) {
        $current = array_shift($queue);
        $visited[] = $current;

        if ($current === $to) {
            return true;
        }

        $currentArr = explode(',', $current);
        foreach ($GLOBALS['OFFSETS'] as $pq) {
            $p = $currentArr[0] + $pq[0];
            $q = $currentArr[1] + $pq[1];
            $neighbour = $p . "," . $q;

            if (isset($board[$neighbour]) || in_array($neighbour, $visited)) {
                continue;
            }
            
            if (canSlide($board, $current, $neighbour)) {
                $queue[] = $neighbour;
            }
        }
    }

    return false;
}