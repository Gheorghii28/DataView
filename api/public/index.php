<?php

namespace Api;

require __DIR__ . '/../../vendor/autoload.php';

use Api\Core\Response;

$response = new Response();

// Global Error & Exception Handling
set_exception_handler(function ($e) use ($response) {
    error_log($e->getMessage());
    $response->internalError('Unexpected server error:'. $e->getMessage());
});

set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($response) {
    error_log("$errstr in $errfile on line $errline");
    $response->internalError("Unexpected server error: $errstr in $errfile on line $errline");
});

require __DIR__ . '/../config/middleware.php';

$router = require __DIR__ . '/../config/routes.php';

$router->run($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);