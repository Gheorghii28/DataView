<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization"); 

$uri = explode('/', trim($_SERVER['REQUEST_URI'], '/')); // Split the URL into parts

if (isset($uri[0]) && $uri[0] === 'api') { // Check if the request is an API call
    require_once __DIR__ . '/../routes/api.php'; // Forward to the API processing file
} else {
    echo json_encode(['message' => 'Invalid API route.']);
}
