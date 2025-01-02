<?php

namespace Api\Controller;

use Api\Core\DbConnection;
use Api\Helper\Helper;
use Api\Core\Response;
use Api\Helper\Validator;
use Api\Model\Table;
use Api\Model\Column;
use Api\Model\Row;

class TableController
{
    private $dbConnection;
    private $db;
    private $tableModel;
    private $columnModel;
    private $rowModel;
    private $validator;
    private $helper;
    private $response;

    public function __construct(DbConnection $dbConnection = NULL, Response $response = NULL) {
        $this->dbConnection = $dbConnection ?? new DbConnection();
        $this->db = $this->dbConnection->getInstance();
        $this->tableModel = new Table($this->db);
        $this->columnModel = new Column($this->db, $this->tableModel);
        $this->rowModel = new Row($this->db);
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

        $resultCheckTableName = $this->validator->checkTableName($tableName);
    
        if (!$resultCheckTableName['success']) {
            return $this->response->error($resultCheckTableName['message']);
        }

        $resultCheckColumns = $this->validator->checkColumns($columns);

        if (!$resultCheckColumns['success']) {
            return $this->response->error($resultCheckColumns['message']);
        }

        if ($this->tableModel->exists($tableName)) {
            return $this->response->conflict("Table '$tableName' already exists.");
        }

        $result = $this->tableModel->create($tableName, $columns);

        if (!$result) {
            return $this->response->internalError('Failed to create table.');
        }

        if (!$this->tableModel->saveTable($userId, $tableName)) {
            return $this->response->internalError('Table created, but failed to link to user.');
        }
        
        return $this->response->success("Table '$tableName' created and linked to user successfully.");
    }

    public function get($request) {
        $validationRequest = $this->validator->validateAndExtractRequest($request, ['userId', 'tableName']);

        if (!$validationRequest['success']) {
            return $this->response->error('Invalid request format or missing fields.');
        }

        extract($validationRequest['data']);
        
        $resultHasAccess = $this->validator->hasAccessToTable($userId, $tableName, fn($userId) => $this->tableModel->getTablesByUser($userId));

        if (!$resultHasAccess['success']) {
            return $this->response->forbidden($resultHasAccess['message']);
        }

        $columns = $this->columnModel->getColumns($tableName);
        $rows = $this->rowModel->getRows($tableName, $userId);

        $data = [
            'columns' => $columns,
            'rows' => $rows
        ];

        return $this->response->success('Table data fetched successfully.', $data);
    }

    public function delete($request) {
        $validationRequest = $this->validator->validateAndExtractRequest($request, ['userId', 'tableName']);

        if (!$validationRequest['success']) {
            return $this->response->error('Invalid request format or missing fields.');
        }

        extract($validationRequest['data']);

        $resultHasAccess = $this->validator->hasAccessToTable($userId, $tableName, fn($userId) => $this->tableModel->getTablesByUser($userId));

        if (!$resultHasAccess['success']) {
            return $this->response->forbidden($resultHasAccess['message']);
        }
    
        $result = $this->tableModel->delete($tableName);
    
        return match ($result['success']) {
            true => $this->response->success($result['message']),
            false => $this->response->internalError($result['message']),
        };
    }

    public function rename($request) {
        $validationRequest = $this->validator->validateAndExtractRequest($request, ['userId', 'oldName', 'newName']);

        if (!$validationRequest['success']) {
            return $this->response->error('Invalid request format or missing fields.');
        }

        extract($validationRequest['data']);

        $resultCheckTableName = $this->validator->checkTableName($newName);
    
        if (!$resultCheckTableName['success']) {
            return $this->response->error($resultCheckTableName['message']);
        }

        $resultHasAccess = $this->validator->hasAccessToTable($userId, $oldName, fn($userId) => $this->tableModel->getTablesByUser($userId));

        if (!$resultHasAccess['success']) {
            return $this->response->forbidden($resultHasAccess['message']);
        }
    
        $result = $this->tableModel->rename($oldName, $newName);
    
        return match ($result['success']) {
            true => $this->response->success($result['message'], ['newName' => $newName]),
            false => $this->response->internalError($result['message']),
        };
    } 

    public function getUserTables($request) {
        $validationRequest = $this->validator->validateAndExtractRequest($request, ['userId']);

        if (!$validationRequest['success']) {
            return $this->response->error('Invalid request format or missing fields.');
        }

        extract($validationRequest['data']);

        if (!$userId) {
            return $this->response->error('User ID is required.');
        }

        $result = $this->tableModel->getTablesByUser($userId);

        return match ($result['success']) {
            true => $this->response->success($result['message'], $result['tables']),
            false => $this->response->internalError('No tables found for this user.'),
        };
    }

    public function updateTableOrder($request) {
        $result = $this->helper->updateOrder($request, $this->tableModel, 'updateTableOrder', ['userId', 'order']);

        return match ($result['success']) {
            true => $this->response->success($result['message']),
            false => $this->response->internalError($result['message']),
        };
    }
}