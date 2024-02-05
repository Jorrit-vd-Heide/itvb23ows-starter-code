<?php

session_start();

include_once 'models/database.php';
include_once 'models/game.php';

$db = src\Models\retrieveDatabase();

$game = new src\Models\Game($db);
$game->restart();
$game->saveStateToSession();

header('Location: views/index.php');