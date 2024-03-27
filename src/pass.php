<?php

session_start();

// Include the necessary models, controllers, and views for the Hive game
include_once '/var/www/html/Models/hiveGameModel.php';
include_once '/var/www/html/Controllers/hiveGameController.php';
include_once '/var/www/html/Views/hiveGameView.php';
include_once '/var/www/html/Models/database.php';

// Create a new database instance and a new Hive game model
$db = retrieveDatabase();
$game = new HiveGameModel($db);

// Create a new Hive game view and controller
$view = new HiveGameView($game);
$controller = new HiveGameController($game, $view);

try {
    $game->loadSession();
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: index.php');
    exit;
}
$controller->pass();
$game->saveState();

header('Location: index.php');