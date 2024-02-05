<?php

namespace src\Views;

$GLOBALS['OFFSETS'] = [[0, 1], [0, -1], [1, 0], [-1, 0], [-1, 1], [1, -1]];

function isNeighbour($a, $b) {
    $a = explode(',', $a);
    $b = explode(',', $b);

    $result = false;

    if (($a[0] == $b[0] && abs($a[1] - $b[1]) == 1) || ($a[1] == $b[1] && abs($a[0] - $b[0]) == 1)) {
        $result = true;
    } elseif ($a[0] + $a[1] == $b[0] + $b[1]) {
        $result = true;
    }
    return $result;
}


function hasNeighBour($a, $board) {
    foreach (array_keys($board) as $b) {
        if (isNeighbour($a, $b)) {
            return true;
        }
    }
}

function neighboursAreSameColor($player, $a, $board) {
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

function len($tile) {
    return $tile ? count($tile) : 0;
}

function slide($board, $from, $to) {
    $result = false;

    if (!hasNeighBour($to, $board) || !!isNeighbour($from, $to)) {
        $result = false;
    } else {
        $b = explode(',', $to);
        $common = [];

        foreach ($GLOBALS['OFFSETS'] as $pq) {
            $p = $b[0] + $pq[0];
            $q = $b[1] + $pq[1];
            if (isNeighbour($from, $p.",".$q)) {
                $common[] = $p.",".$q;
            }
        }
        if (!$board[$common[0]] && !$board[$common[1]] && !$board[$from] && !$board[$to]) {
            $result = false;
        } else {
            $result = min(len($board[$common[0]]), len($board[$common[1]])) 
                <= max(len($board[$from]), len($board[$to]));
        }
    }
    return $result;
}
