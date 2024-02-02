<?php

include_once 'test.php';
include_once 'game.php';
include_once 'database.php';

describe("Dropdown should only show available tiles", function () {
    // arrange
    $db = getDatabase();
    $game = new Game($db);
    $game->restart();
    $player1_hand_before = $game->getHand(0);
    assertNotEqual(array_search('Q', array_keys($player1_hand_before)), false);

    // act
    $game->playTile('Q', '0,0');

    // assert
    $player1_hand_after = $game->getHand(0);
    assertEqual(array_search('Q', array_keys($player1_hand_after)), false);
});