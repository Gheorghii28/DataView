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

    private $db;
    private $tableModel;
    private $columnModel;

    public function __construct() {
        $this->db = DbConnection::getInstance();
        $this->tableModel = new Table($this->db);
        $this->columnModel = new Column($this->db, $this->tableModel);
    }
    
    public function create($request) {
        $data = Validator::validateAndExtractRequest($request, ['userId', 'tableName', 'columns']);
        if (!is_array($data)) return $data;

        extract($data);

        $userTables = $this->tableModel->getTablesByUser($userId);
        $accessCheck = Validator::validateUserTableAccess($userId, $tableName, $userTables);
        if ($accessCheck !== true) return $accessCheck;

        if (!Validator::validateColumns($columns)) {
            Response::error('Invalid columns.');
        }

        $result = $this->columnModel->addColumn($tableName, $columns);

        match ($result['success']) {
            true => Response::success($result['message']),
            false => Response::internalError($result['message']),
        };
    }

    public function rename($request) {
        $data = Validator::validateAndExtractRequest($request, ['userId', 'oldName', 'newName', 'tableName']);
        if (!is_array($data)) return $data;

        extract($data);

        if (!Validator::validateColumnName($newName)) {
            Response::error('Invalid new column name. The column name must start with a letter and contain only letters, numbers, and underscores.');
        }

        $userTables = $this->tableModel->getTablesByUser($userId);
        $accessCheck = Validator::validateUserTableAccess($userId, $tableName, $userTables);
        if ($accessCheck !== true) return $accessCheck;

        $result = $this->columnModel->renameColumn($oldName, $newName, $tableName);

        match ($result['success']) {
            true => Response::success($result['message'], ['newColumnName' => $newName]),
            false => Response::internalError($result['message']),
        };
    }

    public function delete($request) {
        $data = Validator::validateAndExtractRequest($request, ['userId', 'tableName', 'columnName']);
        if (!is_array($data)) return $data;

        extract($data);

        $userTables = $this->tableModel->getTablesByUser($userId);
        $accessCheck = Validator::validateUserTableAccess($userId, $tableName, $userTables);
        if ($accessCheck !== true) return $accessCheck;

        $result = $this->columnModel->deleteColumn($tableName, $columnName);

        match ($result['success']) {
            true => Response::success($result['message']),
            false => Response::internalError($result['message']),
        };
    }

    public function updateColumnOrder($request) {
        return Helper::updateOrder($request, $this->columnModel, 'reorderColumns', ['userId', 'tableName', 'order']);
    }
}