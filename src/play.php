<?php
// Start a new session to store game data
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
    // Load game session data
    $game->loadSession();
    
    // Retrieve selected piece and destination from POST data
    $piece = $_POST['piece'] ?? null;
    $to = $_POST['to'] ?? null;
    
    // Check if required data is provided
    if (!$piece || !$to) {
        handleErrorAndRedirect('Invalid move: missing piece or destination.');
    }

    // Play the selected tile at the specified destination
    $controller->playTile($piece, $to);
    
    // Save the current game state to the database
    $game->saveState();
} catch (Exception $e) {
    // Handle exceptions
    handleErrorAndRedirect($e->getMessage());
}

// Redirect to index page
header('Location: index.php');
exit;
