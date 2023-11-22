<?php

require_once(__DIR__.'/../config/database.php');

$conn = new mysqli(
    $config['server'],
    #Username database
    $config['username'],
    #Password
    $config['password'],
    #Database
    $config['database']
); 