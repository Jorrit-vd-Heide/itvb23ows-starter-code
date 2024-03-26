<?php
// Start a new session to store game data
session_start();

// Include the necessary models, controllers, and views
include_once '/var/www/html/Models/hiveGameModel.php';
include_once '/var/www/html/Controllers/hiveGameController.php';
include_once '/var/www/html/Views/hiveGameView.php';
include_once '/var/www/html/Models/database.php';

// Retrieve the database connection
$db = retrieveDatabase();

// Create a new HiveGameModel instance with the database connection
$game = new HiveGameModel($db);

// Create a new HiveGameView instance with the game model
$view = new HiveGameView($game);

// Create a new HiveGameController instance with the game model and view
$controller = new HiveGameController($game, $view);

// Attempt to load the game session from the database
try {
    $game->loadSession();
} catch (Exception $e) {
    // If an error occurs, store the error message in the session and redirect to the index page
    $_SESSION['error'] = $e->getMessage();
    header('Location: index.php');
    exit;
}

// Retrieve the selected piece and destination from the POST data
$piece = $_POST['piece'];
$to = $_POST['to'];

// Get the hand of the active player
$hand = $controller->getHand($controller->getActivePlayer());

// Play the selected tile at the specified destination
$controller->playTile($piece, $to);

// Save the current game state to the database
$game->saveState();

// Redirect the user to the index page
header('Location: index.php');