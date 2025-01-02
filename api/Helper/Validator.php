<?php

namespace Api\Helper;

use Api\Core\Response;
use Exception;

class Validator {

    private $response;

    public function __construct() {
        $this->response = new Response();
    }

    private function isEmpty($value): bool {
        return empty($value);
    }

    private function startsWithNumber($value): bool {
        return preg_match('/^[0-9]/', $value) === 1;
    }

    private function isValidIdentifier($identifier): bool { 
        // Regex to ensure the identifier starts with a letter and can contain letters, digits, and underscores, with a total length of up to 255 characters.
        $regex = '/^[a-zA-Z][a-zA-Z0-9_]{0,254}$/';

        return preg_match($regex, $identifier) === 1; 
    }

    public function checkTableName($name): array {
        $name = trim($name);
    
        if ($this->isEmpty($name)) {
            return $this->response->errorMessage('Table name cannot be empty.');
        }
    
        if ($this->startsWithNumber($name)) {
            return $this->response->errorMessage('Table name cannot start with a number.');
        }

        if (!$this->isValidIdentifier($name)) {
            return $this->response->errorMessage('Table name can only contain letters, numbers, and underscores.');
        }
    
        return $this->response->successMessage('Table name is valid.');
    }

    public function checkColumns($columns): array {
        if (!is_array($columns)) {
            return $this->response->errorMessage('Invalid columns. Columns must be an array.');
        }

        if (empty($columns)) {
            return $this->response->errorMessage('Invalid columns. Columns array is empty.');
        }

        $validationResult = $this->validateColumns($columns);

        if (!$validationResult['success']) {
            return $this->response->errorMessage($validationResult['message']);
        }

        return $this->response->successMessage('Columns are valid.');
    }

    private function validateColumns($columns): array {
        foreach ($columns as $colName => $colType) { // Check if columns are an array of `name => type` pairs
            if (!$this->isValidIdentifier($colName)) {
                return $this->response->errorMessage("Invalid column name: $colName. Column name can only contain letters, numbers, and underscores.");
            }
            if (!$this->isValidSQLType($colType)) {
                return $this->response->errorMessage("Invalid column type for column '$colName'.");
            }
        }
        return $this->response->successMessage('Columns are valid.');
    }

    private function isValidSQLType($type): bool {
        $type = strtoupper(trim($type));
        $validTypes = [ // List of allowed SQL data types
            'INT', 'VARCHAR', 'TEXT', 'DATE', 'BOOLEAN', 
            'TIMESTAMP', 'DATETIME', 'FLOAT', 'DECIMAL'
        ];

        return preg_match('/^(' . implode('|', $validTypes) . ')(\(\d+\))?$/', $type) === 1; // Check for type with or without length (e.g., VARCHAR(255))
    }

    public function validateColumnName($name): bool {
        return $this->isValidIdentifier($name);
    }

    private function validateRequest($request, $requiredFields): array {
        foreach ($requiredFields as $field) {
            if (!isset($request[$field])) {
                return $this->response->errorMessage("Invalid request. Missing field: $field.");
            }
        }
        return $this->response->successMessage('Validation successful.');
    }
    
    private function checkTableAccess($tableName, $userTables){
        $tableNames = array_column($userTables, 'name');
        if (!in_array($tableName, $tableNames)) {
            return $this->response->errorMessage('Forbidden. You do not have permission to access this table.');
        }
        return $this->response->successMessage('Table access granted.');
    }

    public function validateAndExtractRequest($request, $requiredFields): array {
        $validation = $this->validateRequest($request, $requiredFields);
        if (!$validation['success']) {
            return $this->response->errorMessage($validation['message']);
        }
        $result = array_intersect_key($request, array_flip($requiredFields));
        return $this->response->successMessage('Validation successful.', $result);
    }

    private function validateUserTableAccess($userId, $tableName, $userTables): array {
        if (!$userId) {
            return $this->response->errorMessage('Unauthorized. User ID is missing.');
        }
        
        if (!$tableName) {
            return $this->response->errorMessage('Table name is required.');
        }

        return $this->checkTableAccess($tableName, $userTables);
    }

    public function validateRowIds($db, $tableName, $rowIds): array {
        try {
            $placeholders = implode(',', array_fill(0, count($rowIds), '?'));
            $sql = "SELECT id FROM `$tableName` WHERE id IN ($placeholders)";
            $stmt = $db->prepare($sql);
    
            if (!$stmt) {
                return $this->response->errorMessage("MySQL Error (prepare): " . $db->error);
            }
    
            $types = str_repeat('i', count($rowIds));
            $stmt->bind_param($types, ...$rowIds);
            $stmt->execute();
            $result = $stmt->get_result();
    
            $existingIds = [];
            while ($row = $result->fetch_assoc()) {
                $existingIds[] = $row['id'];
            }
    
            $stmt->close();
    
            if (count($existingIds) !== count($rowIds)) {
                $missingIds = array_diff($rowIds, $existingIds);
                return $this->response->errorMessage("Invalid row ID(s): " . implode(', ', $missingIds));
            } else {
                return $this->response->successMessage('Validation successful.');
            }
        } catch (Exception $e) {
            return $this->response->errorMessage("MySQL Exception: " . $e->getMessage());
        }
    }    

    public function validateRowDataTypes(array $rowData, array $tableColumns): array {
        foreach ($tableColumns as $column) {
            $columnName = $column['name'];
            $columnType = $column['type'];

            if (isset($rowData[$columnName])) {
                if (!$this->validateColumnType($rowData[$columnName], $columnType)) {
                    return $this->response->errorMessage("Invalid data type for column '$columnName'. Expected type: $columnType.");
                }
            }
        }
        return $this->response->successMessage('Validation successful.');
    }

    public function hasAccessToTable($userId, $tableName, callable $getTablesByUser): array {
        $resultTables = $getTablesByUser($userId);
        $resultAccess = $this->validateUserTableAccess($userId, $tableName, $resultTables['tables']);
        return $resultAccess;
    }

    private function validateColumnType($value, $type): bool {
        return match (strtoupper($type)) {
            'INT(11)' => filter_var($value, FILTER_VALIDATE_INT) !== false,
            'VARCHAR(255)' => is_string($value) && strlen($value) <= 255,
            'TEXT' => is_string($value),
            'DATE' => strtotime($value) !== false,
            'BOOLEAN' => is_bool($value) || in_array($value, [0, 1, '0', '1'], true),
            'TIMESTAMP', 'DATETIME' => strtotime($value) !== false,
            'FLOAT', 'DECIMAL' => filter_var($value, FILTER_VALIDATE_FLOAT) !== false,
            default => false,
        };
    }
}
