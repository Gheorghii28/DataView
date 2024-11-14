<?php

require_once "../config/config.php"; // Include configuration files
require_once "data_loader.php"; // Include data loading files
require_once "view_config.php";  // Include the view configuration file

session_start(); // Start session to access user session data

$user_id = $_SESSION['id'] ?? null;
$view = isset($_GET['view']) ? $_GET['view'] : ''; // Get the requested view from the query parameter
$viewBasePath = '../views/'; // Define the base path for views
$viewFile = '../views/404.php'; // Default view file for errors
$data = []; // Initialize data array for the view
$response = "No view selected."; // Default response if no view is selected

if (array_key_exists($view, $allowedViews)) { // Check if the view is in the allowed list
    $viewFile = $viewBasePath . $allowedViews[$view]; // Set the correct view file path

    if (isset($viewDataFunctions[$view])) { // Check if a corresponding data loading function exists for the requested view
        $data = call_user_func($viewDataFunctions[$view], $mysql_db, $user_id); // Load data for the view dynamically
    }
}

if (file_exists($viewFile)) { // Check if the specified view file exists before including it
    ob_start(); // Start output buffering to capture the view's content
    extract($data); // Extract the data array to variables, so $username will be accessible in the view
    include $viewFile; // Include the chosen view file
    $response = ob_get_clean(); // Capture and store the view's output in $response
} else {
    $response = "View file not found."; // Error message if the view file does not exist
}

echo $response; // Output the response to the client
