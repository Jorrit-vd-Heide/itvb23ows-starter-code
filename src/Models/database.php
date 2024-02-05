<?php

namespace src\Models;

include_once 'config.php';

if (!function_exists('src\Models\retrieveDatabase')) {
    function retrieveDatabase() {
        global $database;
        global $config;
        if ($database === null) {
            $database = new \mysqli($config['db_host'], $config['db_user'], $config['db_password'], $config['db_name']);
        }
        return $database;
    }
}
