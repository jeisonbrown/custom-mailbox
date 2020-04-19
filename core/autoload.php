<?php

session_start();

require __DIR__ . '/../vendor/autoload.php';

try {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}
catch (Exception $e) {
}

if (boolval(getenv('DEBUG', false))) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}
