<?php

namespace Api\Controller;

use Api\Core\DbConnection;
use Api\Helper\Helper;
use Api\Helper\Validator;
use Api\Model\Row;
use Api\Model\Table;
use Api\Core\Response;
use Api\Model\Column;

class RowController
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
        $validationRequest = $this->validator->validateAndExtractRequest($request, ['userId', 'tableName', 'data']);

        if (!$validationRequest['success']) {
            return $this->response->error('Invalid request format or missing fields.');
        }

        extract($validationRequest['data']);

        $resultHasAccess = $this->validator->hasAccessToTable($userId, $tableName, fn($userId) => $this->tableModel->getTablesByUser($userId));

        if (!$resultHasAccess['success']) {
            return $this->response->forbidden($resultHasAccess['message']);
        }
        
        $columns = $this->columnModel->getColumns($tableName);
        $dataTypeValidationResult = $this->validator->validateRowDataTypes($data, $columns);

        if(!$dataTypeValidationResult['success']) {
            return $this->response->error($dataTypeValidationResult['message']);
        }

        $result = $this->rowModel->insertRow($tableName, $data);

        return match ($result['success']) {
            true => $this->response->success($result['message'], ['rowId' => $result['rowId'][0]]),
            false => $this->response->internalError($result['message'], ['rowId' => $result['irowId'][0]]),
        };
    }

    public function update($request) {
        $validationRequest = $this->validator->validateAndExtractRequest($request, ['userId', 'tableName', 'data', 'rowId']);

        if (!$validationRequest['success']) {
            return $this->response->error('Invalid request format or missing fields.');
        }

        extract($validationRequest['data']);

        $resultHasAccess = $this->validator->hasAccessToTable($userId, $tableName, fn($userId) => $this->tableModel->getTablesByUser($userId));

        if (!$resultHasAccess['success']) {
            return $this->response->forbidden($resultHasAccess['message']);
        }

        $columns = $this->columnModel->getColumns($tableName);
        $dataTypeValidationResult = $this->validator->validateRowDataTypes($data, $columns);

        if(!$dataTypeValidationResult['success']) {
            return $this->response->error($dataTypeValidationResult['message']);
        }

        $result = $this->rowModel->updateRow($tableName, $rowId, $data);

        return match ($result['success']) {
            true => $this->response->success($result['message']),
            false => $this->response->internalError($result['message']),
        };
    }

    public function delete($request) {
        $validationRequest = $this->validator->validateAndExtractRequest($request, ['userId', 'tableName', 'rowId']);

        if (!$validationRequest['success']) {
            return $this->response->error('Invalid request format or missing fields.');
        }

        extract($validationRequest['data']);

        $resultHasAccess = $this->validator->hasAccessToTable($userId, $tableName, fn($userId) => $this->tableModel->getTablesByUser($userId));

        if (!$resultHasAccess['success']) {
            return $this->response->forbidden($resultHasAccess['message']);
        }
    
        $result = $this->rowModel->deleteRow($tableName, $rowId, $userId);
    
        return match ($result['success']) {
            true => $this->response->success($result['message']),
            false => $this->response->internalError($result['message']),
        };
    }

    public function updateRowOrder($request) {
        $result = $this->helper->updateOrder($request, $this->rowModel, 'reorderRows', ['userId', 'tableName', 'order']);

        return match ($result['success']) {
            true => $this->response->success($result['message']),
            false => $this->response->internalError($result['message']),
        };
    }
}