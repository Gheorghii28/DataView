<?php
// Load Composer's autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Initialize `phpdotenv` and load the `.env` file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Database credentials
if (!defined('DB_SERVER')) {
    define('DB_SERVER', $_ENV['DB_SERVER']);
}
if (!defined('DB_USERNAME')) {
    define('DB_USERNAME', $_ENV['DB_USERNAME']);
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', $_ENV['DB_PASSWORD']);
}
if (!defined('DB_NAME')) {
    define('DB_NAME', $_ENV['DB_NAME']);
}
if (!defined('BASE_API_URL')) {
    define('BASE_API_URL', $_ENV['API_URL']);
}

// Attempt to connect to MySQL database
$mysql_db = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($mysql_db->connect_error) {
    die("Error: Unable to connect " . $mysql_db->connect_error);
}

// Return an array with all the configuration details
return [
    'db_connection' => $mysql_db,
    'base_api_url' => BASE_API_URL,
];