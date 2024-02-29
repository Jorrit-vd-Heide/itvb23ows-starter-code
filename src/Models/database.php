<?php

include_once '/var/www/html/Models/config.php';

function retrieveDatabase() {
    // Check if the database connection object is null
    global $database;
    global $config;
    if ($database === null) {
        // If the database connection object is null, establish a new connection
        $database = new mysqli($config['db_host'], $config['db_user'], $config['db_password'], $config['db_name']);
    }
    // Return the database connection object
    return $database;
}
