<?php

namespace Api\Model;

use Api\Core\Response;
use Api\Helper\Helper;
use Api\Model\Table;
use mysqli;

class Column {

    private $db;
    private $tableModel;
    private $helper;
    private $response;

    public function __construct(mysqli $db, Table $tableModel) {
        $this->db = $db;
        $this->tableModel = $tableModel;
        $this->helper = new Helper();
        $this->response = new Response();
    }

    public function getColumns($tableName) {
        $sql = "DESCRIBE `$tableName`";
        $result = $this->db->query($sql);

        if (!$result) {
            die('Query error: ' . $this->db->error);
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

    public function getColumnType($tableName, $columnName) {
        $sql = "DESCRIBE `$tableName` `$columnName`";
        $result = $this->db->query($sql);

        if ($result && $row = $result->fetch_assoc()) {
            return $row['Type'];
        } else {
            return false;
        }
    }

    public function renameColumn($oldName, $newName, $tableName) {
        if (!$this->tableModel->exists($tableName)) {
            return $this->response->errorMessage("Table '$tableName' does not exist.");
        }

        $columnType = self::getColumnType($tableName, $oldName);
        if ($columnType === false) {
            return $this->response->errorMessage("Column '$oldName' does not exist in table '$tableName'.");
        }
    
        $sql = "ALTER TABLE `$tableName` CHANGE COLUMN `$oldName` `$newName` $columnType";
    
        if ($this->db->query($sql)) {
            return $this->response->successMessage("The column '$oldName' in the '$tableName' table has been successfully renamed to '$newName'.");
        } else {
            return $this->response->errorMessage('Error renaming column: ' . $this->db->error);
        }
    }    

    public function addColumn($tableName, $columns) {
        if (!$this->tableModel->exists($tableName)) {
            return $this->response->errorMessage("Table '$tableName' does not exist.");
        }

        foreach ($columns as $columnName => $columnType) {

            if ($this->helper->existColumnNameInTable($columnName,$tableName, $this->db)) {
                return $this->response->errorMessage("Column '$columnName' does exist.");
            }

            $sql = "ALTER TABLE `$tableName` ADD `$columnName` $columnType";
            
            if (!$this->db->query($sql)) {
                return $this->response->errorMessage('Error adding column: ' . $this->db->error);
            }
        }

        return $this->response->successMessage('Columns successfully added to the table.');
    }

    public function deleteColumn($tableName, $columnName) {
        if (!$this->tableModel->exists($tableName)) {
            return $this->response->errorMessage("Table '$tableName' does not exist.");
        }
    
        $sqlCheckColumn = "SHOW COLUMNS FROM `$tableName` LIKE '$columnName'";
        $result = $this->db->query($sqlCheckColumn);
        if ($result->num_rows === 0) {
            return $this->response->errorMessage("Column '$columnName' does not exist in table '$tableName'.");
        }
    
        $sql = "ALTER TABLE `$tableName` DROP COLUMN `$columnName`";
        if ($this->db->query($sql)) {
            return $this->response->successMessage("Column '$columnName' has been successfully deleted from table '$tableName'.");
        } else {
            return $this->response->errorMessage('Error deleting column: ' . $this->db->error);
        }
    }

    public function reorderColumns($tableName, $newOrder) {
        $columnMetaQuery = "SHOW COLUMNS FROM `$tableName`";
        $result = $this->db->query($columnMetaQuery);
    
        if (!$result) {
            return $this->response->errorMessage("Failed to fetch column metadata: " . $this->db->error);
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
                return $this->response->errorMessage("Column $columnName does not exist in table $tableName.");
            }
    
            $columnInfo = $columnData[$columnName];
            $afterColumn = $index > 0 ? $newOrder[$index - 1] : null;
    
            $query = $afterColumn
                ? "ALTER TABLE `$tableName` MODIFY COLUMN `$columnName` {$columnInfo['type']} {$columnInfo['null']} {$columnInfo['default']} {$columnInfo['extra']} AFTER `$afterColumn`"
                : "ALTER TABLE `$tableName` MODIFY COLUMN `$columnName` {$columnInfo['type']} {$columnInfo['null']} {$columnInfo['default']} {$columnInfo['extra']} FIRST";
    
            if (!$this->db->query($query)) {
                return $this->response->errorMessage("Failed to reorder column $columnName: " . $this->db->error);
            }
        }
    
        return $this->response->successMessage("Column order updated successfully.");
    }
}