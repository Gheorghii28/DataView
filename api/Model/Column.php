<?php

namespace Api\Model;

use Api\Model\Table;
use Api\Core\DbConnection;

class Column {

    public function __construct() {}

    public static function getColumns($tableName) {
        $db = DbConnection::getInstance();
        $sql = "DESCRIBE `$tableName`";
        $result = $db->query($sql);

        if (!$result) {
            die('Query error: ' . $db->error);
        }

        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = [
                'name' => $row['Field'],
                'type' => $row['Type']
            ];
        }

        return $columns;
    }

    public static function getColumnType($tableName, $columnName) {
        $db = DbConnection::getInstance();
        $sql = "DESCRIBE `$tableName` `$columnName`";
        $result = $db->query($sql);

        if ($result && $row = $result->fetch_assoc()) {
            return $row['Type'];
        } else {
            return false;
        }
    }

    public static function renameColumn($oldName, $newName, $tableName) {
        $db = DbConnection::getInstance();
        if (!Table::exists($tableName)) {
            return ['success' => false, 'message' => "Table '$tableName' does not exist."];
        }

        $columnType = self::getColumnType($tableName, $oldName);
        if ($columnType === false) {
            return ['success' => false, 'message' => "Column '$oldName' does not exist in table '$tableName'."];
        }
    
        $sql = "ALTER TABLE `$tableName` CHANGE COLUMN `$oldName` `$newName` $columnType";
    
        if ($db->query($sql)) {
            return ['success' => true, 'message' => "The column '$oldName' in the '$tableName' table has been successfully renamed to '$newName'."];
        } else {
            return ['success' => false, 'message' => 'Error renaming column: ' . $db->error];
        }
    }    

    public static function addColumn($tableName, $columns) {
        $db = DbConnection::getInstance();
        if (!Table::exists($tableName)) {
            return ['success' => false, 'message' => "Table '$tableName' does not exist."];
        }

        foreach ($columns as $columnName => $columnType) {
            $sql = "ALTER TABLE `$tableName` ADD `$columnName` $columnType";
            
            if (!$db->query($sql)) {
                return ['success' => false, 'message' => 'Error adding column: ' . $db->error];
            }
        }

        return ['success' => true, 'message' => 'Columns successfully added to the table.'];
    }

    public static function deleteColumn($tableName, $columnName) {
        $db = DbConnection::getInstance();
        if (!Table::exists($tableName)) {
            return ['success' => false, 'message' => "Table '$tableName' does not exist."];
        }
    
        $sqlCheckColumn = "SHOW COLUMNS FROM `$tableName` LIKE '$columnName'";
        $result = $db->query($sqlCheckColumn);
        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => "Column '$columnName' does not exist in table '$tableName'."];
        }
    
        $sql = "ALTER TABLE `$tableName` DROP COLUMN `$columnName`";
        if ($db->query($sql)) {
            return ['success' => true, 'message' => "Column '$columnName' has been successfully deleted from table '$tableName'."];
        } else {
            return ['success' => false, 'message' => 'Error deleting column: ' . $db->error];
        }
    }

    public static function reorderColumns($tableName, $newOrder) {
        $db = DbConnection::getInstance();
        $columnMetaQuery = "SHOW COLUMNS FROM `$tableName`";
        $result = $db->query($columnMetaQuery);
    
        if (!$result) {
            return ['success' => false, 'message' => "Failed to fetch column metadata: " . $db->error];
        }
    
        $columnData = [];
        while ($row = $result->fetch_assoc()) {
            $columnData[$row['Field']] = [
                'type' => $row['Type'],
                'null' => $row['Null'] === 'YES' ? 'NULL' : 'NOT NULL',
                'default' => $row['Default'] !== null ? "DEFAULT '{$row['Default']}'" : '',
                'extra' => $row['Extra'],
            ];
        }
    
        $result->free();
    
        foreach ($newOrder as $index => $columnName) {
            if (!isset($columnData[$columnName])) {
                return ['success' => false, 'message' => "Column $columnName does not exist in table $tableName."];
            }
    
            $columnInfo = $columnData[$columnName];
            $afterColumn = $index > 0 ? $newOrder[$index - 1] : null;
    
            $query = $afterColumn
                ? "ALTER TABLE `$tableName` MODIFY COLUMN `$columnName` {$columnInfo['type']} {$columnInfo['null']} {$columnInfo['default']} {$columnInfo['extra']} AFTER `$afterColumn`"
                : "ALTER TABLE `$tableName` MODIFY COLUMN `$columnName` {$columnInfo['type']} {$columnInfo['null']} {$columnInfo['default']} {$columnInfo['extra']} FIRST";
    
            if (!$db->query($query)) {
                return ['success' => false, 'message' => "Failed to reorder column $columnName: " . $db->error];
            }
        }
    
        return ['success' => true, 'message' => 'Column order updated successfully.'];
    }
}