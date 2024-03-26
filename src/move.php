<?php
// Start a new session to store game data
session_start();

// Include the necessary models, controllers, and views for the Hive game
include_once '/var/www/html/Models/hiveGameModel.php';
include_once '/var/www/html/Controllers/hiveGameController.php';
include_once '/var/www/html/Views/hiveGameView.php';
include_once '/var/www/html/Models/database.php';

// Retrieve the piece to be moved and the destination tile
$piece = $_POST['from'];
$to = $_POST['to'];

// Create a new database instance and a new Hive game model
$db = retrieveDatabase();
$game = new HiveGameModel($db);

// Create a new Hive game view and controller
$view = new HiveGameView($game);
$controller = new HiveGameController($game, $view);

// Load the game session data
try {
    $game->loadSession();
} catch (Exception $e) {
    // If there is an error loading the session data, store the error message in the session variable
    $_SESSION['error'] = $e->getMessage();
    // Redirect the user to the index page
    header('Location: index.php');
    exit;
}

// Move the selected piece to the destination tile
$controller->moveTile($piece, $to);

// Save the updated game state
$game->saveState();

// Redirect the user to the index page
header('Location: index.php');