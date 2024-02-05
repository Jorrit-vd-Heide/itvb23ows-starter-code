<?php

include_once 'config.php';

function retrieveDatabase() {
    return new mysqli($config['db_host'], $config['db_user'], $config['db_password'], $config['db_name']);
}
