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

    public function delete($request) {
        if (!isset($request['userId'])) { // Check if the request contains a user ID
            return jsonResponse(401, 'Unauthorized. User ID is missing.');
        }
    
        if (!isset($request['tableName'])) { // Validate that a table name is provided
            return jsonResponse(400, 'Invalid request. Table name is required.');
        }
    
        $userId = $request['userId'];
        $tableName = $request['tableName'];
    
        // Verify that the table belongs to the user
        $userTables = $this->tableModel->getTablesByUser($userId);
        if (!in_array($tableName, $userTables)) {
            return jsonResponse(403, 'Forbidden. You do not have permission to delete this table.');
        }
    
        $result = $this->tableModel->delete($tableName); // Attempt to delete the table
    
        // Return appropriate response based on deletion result
        if ($result['success']) {
            return jsonResponse(200, $result['message']);
        } else {
            return jsonResponse(500, $result['message']);
        }
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
            return jsonResponse(200, $result['message'], ['newTableName' => $newName]);
        } else {
            return jsonResponse(500, $result['message']);
        }
    }    

    public function renameColumn($request) {
        // Check if the request contains the required parameters
        if (!isset($request['userId'])) {
            return jsonResponse(401, 'Unauthorized. User ID is missing.');
        }

        if (!isset($request['oldName']) || !isset($request['newName']) || !isset($request['tableName'])) {
            return jsonResponse(400, 'Invalid request. Old name, new name, and table name are required.');
        }

        $userId = $request['userId'];
        $tableName = $request['tableName'];
        $oldName = $request['oldName'];
        $newName = $request['newName'];

        if (!Validator::validateColumnName($newName)) { // Check if the new column name is valid
            return jsonResponse(400, 'Invalid new column name. The column name must start with a letter and contain only letters, numbers, and underscores.');
        }

        $userTables = $this->tableModel->getTablesByUser($userId); // Check if the table exists and the user has permission to edit it
        if (!in_array($tableName, $userTables)) {
            return jsonResponse(403, 'Forbidden. You do not have permission to rename a column in this table.');
        }

        $result = $this->tableModel->renameColumn($oldName, $newName, $tableName); // Call the model method to rename the column

        if ($result['success']) {
            return jsonResponse(200, $result['message'], ['newColumnName' => $newName]);
        } else {
            return jsonResponse(500, $result['message']);
        }
    }    

    public function addColumn($request) {
        // Check if the required data is present
        if (!isset($request['userId'])) { 
            return jsonResponse(401, 'Unauthorized. User ID is missing.');
        }

        if (!isset($request['name']) || !isset($request['columns'])) {
            return jsonResponse(400, 'Invalid request. Table name and columns are required.');
        }

        $userId = $request['userId'];
        $tableName = $request['name'];
        $columns = $request['columns'];  // An array of column names and types

        // Verify if the table belongs to the user
        $userTables = $this->tableModel->getTablesByUser($userId);
        if (!in_array($tableName, $userTables)) {
            return jsonResponse(403, 'Forbidden. You do not have permission to add columns to this table.');
        }

        $result = $this->tableModel->addColumn($tableName, $columns); // Add columns to the table

        if ($result['success']) {
            return jsonResponse(200, $result['message']);
        } else {
            return jsonResponse(500, $result['message']);
        }
    }

    public function deleteColumn($request) {
        // Check if the required parameters are present
        if (!isset($request['userId'])) {
            return jsonResponse(401, 'Unauthorized. User ID is missing.');
        }
    
        if (!isset($request['tableName']) || !isset($request['columnName'])) {
            return jsonResponse(400, 'Invalid request. Table name and column name are required.');
        }
    
        $userId = $request['userId'];
        $tableName = $request['tableName'];
        $columnName = $request['columnName'];
    
        // Verify if the table belongs to the user
        $userTables = $this->tableModel->getTablesByUser($userId);
        if (!in_array($tableName, $userTables)) {
            return jsonResponse(403, 'Forbidden. You do not have permission to delete columns in this table.');
        }
    
        $result = $this->tableModel->deleteColumn($tableName, $columnName); // Delete the column
    
        if ($result['success']) {
            return jsonResponse(200, $result['message']);
        } else {
            return jsonResponse(500, $result['message']);
        }
    }    
}
