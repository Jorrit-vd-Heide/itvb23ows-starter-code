<?php

session_start();

include_once '/var/www/html/Models/hiveGameModel.php';
include_once '/var/www/html/Controllers/hiveGameController.php';
include_once '/var/www/html/Views/hiveGameView.php';
include_once '/var/www/html/Models/database.php';

$db = retrieveDatabase();
$game = new HiveGameModel($db);
$view = new HiveGameView($game);
$controller = new HiveGameController($game, $view);
try {
    $game->loadSession();
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: index.php');
    exit;
}

$controller->undo();
$game->saveStateToSession();

header('Location: index.php');