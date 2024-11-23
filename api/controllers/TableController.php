<?php

require_once __DIR__ . '/../models/Table.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class TableController {
    private $tableModel;

    public function __construct() {
        $this->tableModel = new Table();
    }

    public function create($request) {

        if (!isset($request['userId'])) {
            return jsonResponse(401, 'Unauthorized. User ID is missing.');
        }
        
        if (!isset($request['name']) || !isset($request['columns'])) {
            return jsonResponse(400, 'Invalid request. Name and columns are required.');
        }

        $userId = $request['userId'];
        $tableName = $request['name'];
        $columns = $request['columns']; // Ex: ['name' => 'VARCHAR(255)', 'age' => 'INT']

        if (!Validator::validateTableName($tableName)) {
            return jsonResponse(400, 'Invalid table name. Use only letters, numbers, and underscores.');
        }

        if (!Validator::validateColumns($columns)) {
            return jsonResponse(400, 'Invalid columns. Ensure valid names and SQL types.');
        }

        if ($this->tableModel->exists($tableName)) {
            return jsonResponse(409, "Table '$tableName' already exists.");
        }

        $result = $this->tableModel->create($tableName, $columns);

        if ($result) {
            $saved = $this->tableModel->saveTable($userId, $tableName); // Save the table in the user_tables table
            if ($saved) {
                return jsonResponse(200, "Table '$tableName' created and linked to user successfully.");
            }
            return jsonResponse(500, "Table created, but failed to link to user.");
        }
        return jsonResponse(500, "Failed to create table.");
    }

    public function getUserTables($userId) {
        if (!$userId) {
            return jsonResponse(400, 'User ID is required.');
        }

        $userTables = $this->tableModel->getTablesByUser($userId);

        if (!empty($userTables)) {
            return jsonResponse(200, 'User tables fetched successfully.', $userTables);
        } else {
            return jsonResponse(404, 'No tables found for this user.');
        }
    }

    public function renameTable($request) {
        if (!isset($request['userId'])) {
            return jsonResponse(401, 'Unauthorized. User ID is missing.');
        }
    
        if (!isset($request['oldName']) || !isset($request['newName'])) {
            return jsonResponse(400, 'Invalid request. Old name and new name are required.');
        }
    
        $userId = $request['userId'];
        $oldName = $request['oldName'];
        $newName = $request['newName'];
    
        if (!Validator::validateTableName($newName)) {
            return jsonResponse(400, 'Invalid new table name. Use only letters, numbers, and underscores.');
        }
    
        $userTables = $this->tableModel->getTablesByUser($userId);
        if (!in_array($oldName, $userTables)) { // Check if the table belongs to the user
            return jsonResponse(403, 'Forbidden. You do not have permission to rename this table.');
        }
    
        $result = $this->tableModel->rename($oldName, $newName); // Rename the table
    
        if ($result['success']) {
            return jsonResponse(200, $result['message']);
        } else {
            return jsonResponse(500, $result['message']);
        }
    }    
}
