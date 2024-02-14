<?php

$GLOBALS['OFFSETS'] = [[0, 1], [0, -1], [1, 0], [-1, 0], [-1, 1], [1, -1]];

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

function hasNeighBour($a, $board)
{
    foreach (array_keys($board) as $b) {
        if (isNeighbour($a, $b)) {
            return true;
        }

    }
}

function pathContainsEmptyTiles($from, $to, $board)
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

function len($tile)
{
    return $tile ? count($tile) : 0;
}

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

function hasSlidePath($board, $from, $to, $isSpider = false)
{
    $toVisit = [$from];
    $visited = [];
    $paths = [];
    $paths[$from] = [[$from]];

    while (count($toVisit) > 0) {
        $current = array_pop($toVisit);

        if ($current == $to) {
            $visited[] = $current;
            
            if ($isSpider) {
                foreach ($paths[$current] as $path) {
                    if (count($path) - 1 == 3) {
                        return true;
                    }
                }
            } else {
                return true;
            }
        }
        $visited[] = $current;
        $currentArr = explode(',', $current);
        foreach ($GLOBALS['OFFSETS'] as $pq) {
            $p = $currentArr[0] + $pq[0];
            $q = $currentArr[1] + $pq[1];
            $neighbour = $p . "," . $q;
            if (isset($board[$neighbour])) {
                continue;
            }
            $temp = $board[$from];
            unset($board[$from]);
            if (!in_array($neighbour, $visited) && canSlide($board, $current, $neighbour, false, $isSpider)) {
                $toVisit[] = $neighbour;
                foreach ($paths[$current] as $path) {
                    $newPath = $path;
                    $newPath[] = $neighbour;
                    $paths[$neighbour][] = $newPath;
                }
            } 
            $board[$from] = $temp;
        }
    }
    return false;
}