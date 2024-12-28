<?php

namespace Api\Helper;

use Api\Core\Response;
use Exception;

class Validator {

    private $response;

    public function __construct() {
        $this->response = new Response();
    }
    public function validateTableName($name): bool {
        return preg_match('/^[a-zA-Z0-9_]+$/', $name) === 1; // Check if the name contains only letters, numbers, and underscores
    }

    public function validateColumns($columns): bool {
        foreach ($columns as $colName => $colType) { // Check if columns are an array of `name => type` pairs
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $colName)) {
                return false; // Invalid column name
            }
            if (!self::isValidSQLType($colType)) {
                return false; // Invalid SQL data type
            }
        }
        return true;
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
        return preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $name) === 1; // The column name must consist only of letters, numbers, and underscores and must start with a letter (no numbers at the beginning).
    }

    private function validateRequest($request, $requiredFields): array {
        foreach ($requiredFields as $field) {
            if (!isset($request[$field])) {
                return ['success' => false, 'message' => "Invalid request. Missing field: $field."];
            }
        }
        return ['success' => true, 'message' => 'Validation successful.'];
    }
    
    private function checkTableAccess($tableName, $userTables){
        $tableNames = array_column($userTables, 'name');
        if (!in_array($tableName, $tableNames)) {
            return ['success' => false, 'message' => 'Forbidden. You do not have permission to access this table.'];
        }
        return ['success' => true, 'message' => 'Table access granted.'];
    }

    public function validateAndExtractRequest($request, $requiredFields): array {
        $validation = self::validateRequest($request, $requiredFields);
        if (!$validation['success']) {
            return ['success' => false, 'message' => $validation['message']];
        }
        $result = array_intersect_key($request, array_flip($requiredFields));
        return ['success' => true, 'message' => 'Validation successful.', 'data' => $result];
    }

    private function validateUserTableAccess($userId, $tableName, $userTables): array {
        if (!$userId) {
            return ['success' => false, 'message' => 'Unauthorized. User ID is missing.'];
        }
        
        if (!$tableName) {
            return ['success' => false, 'message' => 'Table name is required.'];
        }

        return $this->checkTableAccess($tableName, $userTables);
    }

    public function validateRowIds($db, $tableName, $rowIds): array {
        try {
            $placeholders = implode(',', array_fill(0, count($rowIds), '?'));
            $sql = "SELECT id FROM `$tableName` WHERE id IN ($placeholders)";
            $stmt = $db->prepare($sql);
    
            if (!$stmt) {
                return ['success' => false, 'message' => "MySQL Error (prepare): " . $db->error];
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
                return ['success' => false, 'message' => "Invalid row ID(s): " . implode(', ', $missingIds)];
            } else {
                return ['success' => true, 'message' => 'Validation successful.'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => "MySQL Exception: " . $e->getMessage()];
        }
    }    

    public function validateDataTypes(array $data, string $tableName, $columnModel): array {
        $columns = $columnModel->getColumns($tableName);

        foreach ($columns as $column) {
            $columnName = $column['name'];
            $columnType = $column['type'];

            if (isset($data[$columnName])) {
                if (!$this->validateColumnType($data[$columnName], $columnType)) {
                    return [
                        'success' => false,
                        'message' => "Invalid data type for column '$columnName'. Expected type: $columnType."
                    ];
                }
            }
        }

        return ['success' => true, 'message' => 'Validation successful.'];
    }

    public function hasAccessToTable($userId, $tableName, $tableModel): array {
        $result = $tableModel->getTablesByUser($userId);
        $result = $this->validateUserTableAccess($userId, $tableName, $result['tables']);
        return $result;
    }

    public function validateColumnType($value, $type): bool {
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
