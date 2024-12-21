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
    private $db;
    private $tableModel;
    private $columnModel;
    private $rowModel;

    public function __construct() {
        $this->db = DbConnection::getInstance();
        $this->tableModel = new Table($this->db);
        $this->columnModel = new Column($this->db, $this->tableModel);
        $this->rowModel = new Row($this->db);
    }

    public function create($request) {
        $data = Validator::validateAndExtractRequest($request, ['userId', 'tableName', 'columns']);
        if (!is_array($data)) return $data;

        extract($data);

        if (!Validator::validateTableName($tableName)) {
            Response::error('Invalid table name. Use only letters, numbers, and underscores.');
        }

        if (!Validator::validateColumns($columns)) {
            Response::error('Invalid columns. Ensure valid names and SQL types.');
        }

        if ($this->tableModel->exists($tableName)) {
            Response::conflict("Table '$tableName' already exists.");
        }

        $result = $this->tableModel->create($tableName, $columns);

        if (!$result) {
            Response::internalError('Failed to create table.');
        }

        if (!$this->tableModel->saveTable($userId, $tableName)) {
            Response::internalError('Table created, but failed to link to user.');
        }
        
        Response::success("Table '$tableName' created and linked to user successfully.");
    }

    public function get($request) {
        $data = Validator::validateAndExtractRequest($request, ['userId', 'tableName']);
        if (!is_array($data)) return $data;

        extract($data);
        
        $userTables = $this->tableModel->getTablesByUser($userId);
        $accessCheck = Validator::validateUserTableAccess($userId, $tableName, $userTables);
        if ($accessCheck !== true) return $accessCheck;

        $columns = $this->columnModel->getColumns($tableName);
        $rows = $this->rowModel->getRows($tableName, $userId);

        $data = [
            'columns' => $columns,
            'rows' => $rows
        ];

        Response::success('Table data fetched successfully.', $data);
    }

    public function delete($request) {
        $data = Validator::validateAndExtractRequest($request, ['userId', 'tableName']);
        if (!is_array($data)) return $data;

        extract($data);

        $userTables = $this->tableModel->getTablesByUser($userId);
        $accessCheck = Validator::validateUserTableAccess($userId, $tableName, $userTables);
        if ($accessCheck !== true) return $accessCheck;
    
        $result = $this->tableModel->delete($tableName);
    
        match ($result['success']) {
            true => Response::success($result['message']),
            false => Response::internalError($result['message']),
        };
    }

    public function rename($request) {
        $data = Validator::validateAndExtractRequest($request, ['userId', 'oldName', 'newName']);
        if (!is_array($data)) return $data;

        extract($data);
    
        if (!Validator::validateTableName($newName)) {
            Response::error('Invalid new table name. Use only letters, numbers, and underscores.');
        }

        $userTables = $this->tableModel->getTablesByUser($userId);
        $accessCheck = Validator::validateUserTableAccess($userId, $oldName, $userTables);
        if ($accessCheck !== true) return $accessCheck;
    
        $result = $this->tableModel->rename($oldName, $newName);
    
        match ($result['success']) {
            true => Response::success($result['message'], ['newTableName' => $newName]),
            false => Response::internalError($result['message']),
        };
    } 

    public function getUserTables($request) {
        $data = Validator::validateAndExtractRequest($request, ['userId']);
        if (!is_array($data)) return $data;

        extract($data);

        if (!$userId) {
            Response::error('User ID is required.');
        }

        $userTables = $this->tableModel->getTablesByUser($userId);

        match (!empty($userTables)) {
            true => Response::success('User tables fetched successfully.', $userTables),
            false => Response::internalError('No tables found for this user.'),
        };
    }

    public function updateTableOrder($request) {
        return Helper::updateOrder($request, $this->tableModel, 'updateTableOrder', ['userId', 'order']);
    }
}