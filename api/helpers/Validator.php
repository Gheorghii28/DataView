<?php

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
}
