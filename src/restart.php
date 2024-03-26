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

// Restart the game
$controller->restart();

// Save the current game state
$game->saveState();

// Redirect the user to the index page
header('Location: index.php');