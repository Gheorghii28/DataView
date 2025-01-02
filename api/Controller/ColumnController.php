<?php

namespace Api\Controller;

use Api\Core\DbConnection;
use Api\Helper\Helper;
use Api\Helper\Validator;
use Api\Model\Column;
use Api\Model\Table;
use Api\Core\Response;

class ColumnController
{

    private $dbConnection;
    private $db;
    private $tableModel;
    private $columnModel;
    private $validator;
    private $helper;
    public $response;

    public function __construct(DbConnection $dbConnection = NULL, Response $response = NULL) {
        $this->dbConnection = $dbConnection ?? new DbConnection();
        $this->db = $this->dbConnection->getInstance();
        $this->tableModel = new Table($this->db);
        $this->columnModel = new Column($this->db, $this->tableModel);
        $this->validator = new Validator();
        $this->helper = new Helper();
        $this->response = $response ?? new Response();
    }
    
    public function create($request) {
        $validationRequest = $this->validator->validateAndExtractRequest($request, ['userId', 'tableName', 'columns']);

        if (!$validationRequest['success']) {
            return $this->response->error('Invalid request format or missing fields.');
        }

        extract($validationRequest['data']);

        $resultHasAccess = $this->validator->hasAccessToTable($userId, $tableName, fn($userId) => $this->tableModel->getTablesByUser($userId));

        if (!$resultHasAccess['success']) {
            return $this->response->forbidden($resultHasAccess['message']);
        }

        $resultCheckColumns = $this->validator->checkColumns($columns);

        if (!$resultCheckColumns['success']) {
            return $this->response->error($resultCheckColumns['message']);
        }

        $result = $this->columnModel->addColumn($tableName, $columns);

        return match ($result['success']) {
            true => $this->response->success($result['message']),
            false => $this->response->internalError($result['message']),
        };
    }

    public function rename($request) {
        $validationRequest = $this->validator->validateAndExtractRequest($request, ['userId', 'oldName', 'newName', 'tableName']);

        if (!$validationRequest['success']) {
            return $this->response->error('Invalid request format or missing fields.');
        }

        extract($validationRequest['data']);

        if (!$this->validator->validateColumnName($newName)) {
            return $this->response->error('Invalid new column name. The column name must start with a letter and contain only letters, numbers, and underscores.');
        }

        $resultHasAccess = $this->validator->hasAccessToTable($userId, $tableName, fn($userId) => $this->tableModel->getTablesByUser($userId));

        if (!$resultHasAccess['success']) {
            return $this->response->forbidden($resultHasAccess['message']);
        }

        $result = $this->columnModel->renameColumn($oldName, $newName, $tableName);

        return match ($result['success']) {
            true => $this->response->success($result['message'], ['newColumnName' => $newName]),
            false => $this->response->internalError($result['message']),
        };
    }

    public function delete($request) {
        $validationRequest = $this->validator->validateAndExtractRequest($request, ['userId', 'tableName', 'columnName']);
        
        if (!$validationRequest['success']) {
            return $this->response->error('Invalid request format or missing fields.');
        }

        extract($validationRequest['data']);

        $resultHasAccess = $this->validator->hasAccessToTable($userId, $tableName, fn($userId) => $this->tableModel->getTablesByUser($userId));

        if (!$resultHasAccess['success']) {
            return $this->response->forbidden($resultHasAccess['message']);
        }

        $result = $this->columnModel->deleteColumn($tableName, $columnName);

        return match ($result['success']) {
            true => $this->response->success($result['message']),
            false => $this->response->internalError($result['message']),
        };
    }

    public function updateColumnOrder($request) {
        $result = $this->helper->updateOrder($request, $this->columnModel, 'reorderColumns', ['userId', 'tableName', 'order']);
        
        return match ($result['success']) {
            true => $this->response->success($result['message']),
            false => $this->response->internalError($result['message']),
        };
    }
}