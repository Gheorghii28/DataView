<?php
// Load Composer's autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Initialize `phpdotenv` and load the `.env` file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Database credentials
define('DB_SERVER', $_ENV['DB_SERVER']);
define('DB_USERNAME', $_ENV['DB_USERNAME']);
define('DB_PASSWORD', $_ENV['DB_PASSWORD']);
define('DB_NAME', $_ENV['DB_NAME']);

// Attempt to connect to MySQL database
$mysql_db = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($mysql_db->connect_error) {
    die("Error: Unable to connect " . $mysql_db->connect_error);
}