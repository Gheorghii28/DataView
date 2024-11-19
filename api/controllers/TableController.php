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
        
        if (!isset($request['name']) || !isset($request['columns'])) {
            return jsonResponse(400, 'Invalid request. Name and columns are required.');
        }

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
            return jsonResponse(200, "Table '$tableName' created successfully.");
        }
        return jsonResponse(500, "Failed to create table.");
    }
}
