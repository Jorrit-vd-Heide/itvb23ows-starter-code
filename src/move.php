<?php
session_start();

include_once '/var/www/html/Models/hiveGameModel.php';
include_once '/var/www/html/Controllers/hiveGameController.php';
include_once '/var/www/html/Views/hiveGameView.php';
include_once '/var/www/html/Models/database.php';

$piece = $_POST['from'];
$to = $_POST['to'];

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

$controller->moveTile($piece, $to);
$game->saveState();

header('Location: index.php');
