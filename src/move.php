<?php
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

// Retrieve POST data
$piece = $_POST['from'] ?? null;
$to = $_POST['to'] ?? null;

// Check if required data is provided
if (!$piece || !$to) {
    handleErrorAndRedirect('Invalid move: missing piece or destination.');
}

// Create instances
$db = retrieveDatabase();
$game = new HiveGameModel($db);
$view = new HiveGameView($game);
$controller = new HiveGameController($game, $view);

try {
    // Load game session data
    $game->loadSession();
    
    // Move the piece
    $controller->moveTile($piece, $to);
    
    // Save the updated game state
    $game->saveState();
} catch (Exception $e) {
    // Handle exceptions
    handleErrorAndRedirect($e->getMessage());
}

// Redirect to index page
header('Location: index.php');
exit;
