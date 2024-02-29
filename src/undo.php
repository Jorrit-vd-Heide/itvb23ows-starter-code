<?php

// Start a new session to store the game state
session_start();

// Include the necessary classes for the game
include_once '/var/www/html/Models/hiveGameModel.php';
include_once '/var/www/html/Controllers/hiveGameController.php';
include_once '/var/www/html/Views/hiveGameView.php';
include_once '/var/www/html/Models/database.php';

// Retrieve the database connection
$db = retrieveDatabase();

// Create a new game model with the database connection
$game = new HiveGameModel($db);

// Create a new game view with the game model
$view = new HiveGameView($game);

// Create a new game controller with the game model and game view
$controller = new HiveGameController($game, $view);

// Try to load the game state from the session
try {
    $game->loadSession();
} catch (Exception $e) {
    // If there is an error loading the game state, store the error message in the session
    $_SESSION['error'] = $e->getMessage();

    // Redirect the user to the index page
    header('Location: index.php');
    exit;
}

// Call the undo method on the game controller to undo the last move
$controller->undo();

// Save the updated game state to the session
$game->saveStateToSession();

// Redirect the user to the index page
header('Location: index.php');