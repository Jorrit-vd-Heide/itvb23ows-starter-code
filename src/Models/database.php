<?php

include_once '/var/www/html/Models/config.php';

function retrieveDatabase() {
    global $database;
    global $config;
    if ($database === null) {
        $database = new mysqli($config['db_host'], $config['db_user'], $config['db_password'], $config['db_name']);
    }
    return $database;
}
