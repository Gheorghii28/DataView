<?php

namespace Api\Helper;

use Api\Core\Response;
use Exception;

class Validator {
    public static function validateTableName($name) {
        return preg_match('/^[a-zA-Z0-9_]+$/', $name); // Check if the name contains only letters, numbers, and underscores
    }

    public static function validateColumns($columns) {
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

    private static function isValidSQLType($type) {
        $validTypes = [ // List of allowed SQL data types
            'INT', 'VARCHAR', 'TEXT', 'DATE', 'BOOLEAN', 
            'TIMESTAMP', 'DATETIME', 'FLOAT', 'DECIMAL'
        ];

        return preg_match('/^(' . implode('|', $validTypes) . ')(\(\d+\))?$/', $type); // Check for type with or without length (e.g., VARCHAR(255))
    }

    public static function validateColumnName($name) {
        return preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $name); // The column name must consist only of letters, numbers, and underscores and must start with a letter (no numbers at the beginning).
    }

    private static function validateRequest($request, $requiredFields) {
        foreach ($requiredFields as $field) {
            if (!isset($request[$field])) {
                Response::error("Invalid request. Missing field: $field.");
            }
        }
        return true;
    }
    
    public static function checkTableAccess($tableName, $userTables) {
        $tableNames = array_column($userTables, 'name');
        if (!in_array($tableName, $tableNames)) {
            Response::forbidden('Forbidden. You do not have permission to access this table.');
        }
        return true;
    }

    public static function validateAndExtractRequest($request, $requiredFields) {
        $validation = self::validateRequest($request, $requiredFields);
        if ($validation !== true) return $validation;
        return array_intersect_key($request, array_flip($requiredFields));
    }

    public static function validateUserTableAccess($userId, $tableName, $userTables) {
        if (!$userId) {
            Response::unauthorized('Unauthorized. User ID is missing.');
        }
        
        if (!$tableName) {
            Response::error('Table name is required.');
        }

        return Validator::checkTableAccess($tableName, $userTables);
    }

    public static function validateRowIds($db, $tableName, $rowIds) {
        $placeholders = implode(',', array_fill(0, count($rowIds), '?'));
        $sql = "SELECT id FROM `$tableName` WHERE id IN ($placeholders)";
        $stmt = $db->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $db->error);
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
            throw new Exception("Invalid rowId(s) detected.");
        }
    }
}
