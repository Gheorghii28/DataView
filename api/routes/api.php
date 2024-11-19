<?php
$requestMethod = $_SERVER['REQUEST_METHOD'];
$uri = explode('/', trim($_SERVER['REQUEST_URI'], '/')); // Split the URL into parts

if ($uri[1] === 'table') {
    require_once __DIR__ . '/../controllers/TableController.php';

    $tableController = new TableController();

    switch ($requestMethod) {
        case 'POST':
            $requestData = json_decode(file_get_contents('php://input'), true);
            echo $tableController->create($requestData);
            break;

        // Add additional methods like PUT or DELETE here as needed

        default:
            echo json_encode(['message' => 'Method not allowed.']);
            break;
    }
} else {
    echo json_encode(['message' => 'Endpoint not found.']);
}

// Comment: You can extend this script to handle other endpoints by adding more `if` conditions
