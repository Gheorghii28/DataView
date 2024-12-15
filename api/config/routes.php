<?php

use Api\Core\Response;
use Api\Core\Router;
use Api\Controller\TableController;
use Api\Controller\ColumnController;
use Api\Controller\RowController;

$router = new Router();

$router->get('/api/table', TableController::class . '::get');
$router->post('/api/table', TableController::class . '::create');
$router->put('/api/renameTable', TableController::class . '::rename');
$router->delete('/api/table', TableController::class . '::delete');

$router->get('/api/user/tables', TableController::class . '::getUserTables');
$router->post('/api/user/tables', TableController::class . '::updateTableOrder');

$router->post('/api/reorder/columns', ColumnController::class . '::updateColumnOrder');
$router->post('/api/column', ColumnController::class . '::create');
$router->put('/api/column', ColumnController::class . '::rename');
$router->delete('/api/column', ColumnController::class . '::delete');

$router->post('/api/reorder/rows', RowController::class . '::updateRowOrder');
$router->post('/api/rows', RowController::class . '::create');
$router->put('/api/rows', RowController::class . '::update');
$router->delete('/api/rows', RowController::class . '::delete');

$router->addNotFoundHandler(function () {
    Response::notFound();
});

return $router;
