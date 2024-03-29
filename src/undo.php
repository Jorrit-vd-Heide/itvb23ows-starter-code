<?php

// Start a new session to store the game state
session_start();

// Include necessary files
include_once '/var/www/html/Models/hiveGameModel.php';
include_once '/var/www/html/Controllers/hiveGameController.php';
include_once '/var/www/html/Views/hiveGameView.php';
include_once '/var/www/html/Models/database.php';

// Function to handle errors and redirect
function handleErrorAndRedirect($errorMessage) {
    $_SESSION['error'] = $errorMessage;
    header('Location: index.php');
    exit;
}

// Retrieve database connection
$db = retrieveDatabase();

// Create instances
$game = new HiveGameModel($db);
$view = new HiveGameView($game);
$controller = new HiveGameController($game, $view);

try {
    // Load game state from session
    $game->loadSession();
    
    // Undo the last move
    $controller->undo();
    
    // Save updated game state to session
    $game->saveState();
} catch (Exception $e) {
    // Handle exceptions
    handleErrorAndRedirect($e->getMessage());
}

// Redirect to index page
header('Location: index.php');
exit;
