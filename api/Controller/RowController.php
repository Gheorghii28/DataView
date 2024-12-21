<?php

namespace Api\Controller;

use Api\Core\DbConnection;
use Api\Helper\Helper;
use Api\Helper\Validator;
use Api\Model\Row;
use Api\Model\Table;
use Api\Core\Response;

class RowController
{

    private $db;
    private $tableModel;
    private $rowModel;

    public function __construct() {
        $this->db = DbConnection::getInstance();
        $this->tableModel = new Table($this->db);
        $this->rowModel = new Row($this->db);
    }
    
    public function create($request) {
        $data = Validator::validateAndExtractRequest($request, ['userId', 'tableName', 'data']);
        if (!is_array($data)) return $data;

        extract($data);
        
        $userTables = $this->tableModel->getTablesByUser($userId);
        $accessCheck = Validator::validateUserTableAccess($userId, $tableName, $userTables);
        if ($accessCheck !== true) return $accessCheck;

        $result = $this->rowModel->insertRow($tableName, $data);

        match ($result['success']) {
            true => Response::success($result['message'], ['rowId' => $result['id']]),
            false => Response::internalError($result['message'], ['rowId' => $result['id']]),
        };
    }

    public function update($request) {
        $data = Validator::validateAndExtractRequest($request, ['userId', 'tableName', 'data', 'rowId']);
        if (!is_array($data)) return $data;

        extract($data);
        
        $userTables = $this->tableModel->getTablesByUser($userId);
        $accessCheck = Validator::validateUserTableAccess($userId, $tableName, $userTables);
        if ($accessCheck !== true) return $accessCheck;

        $result = $this->rowModel->updateRow($tableName, $rowId, $data);

        match ($result['success']) {
            true => Response::success($result['message']),
            false => Response::internalError($result['message']),
        };
    }

    public function delete($request) {
        $data = Validator::validateAndExtractRequest($request, ['userId', 'tableName', 'rowId']);
        if (!is_array($data)) return $data;

        extract($data);
    
        $result = $this->rowModel->deleteRow($tableName, $rowId, $userId);
    
        match ($result['success']) {
            true => Response::success($result['message']),
            false => Response::internalError($result['message']),
        };
    }

    public function updateRowOrder($request) {
        return Helper::updateOrder($request, $this->rowModel, 'reorderRows', ['userId', 'tableName', 'order']);
    }
}