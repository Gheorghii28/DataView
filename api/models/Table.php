<?php

require_once __DIR__ . '/../../config/config.php';

class Table {
    private $mysqli;

    public function __construct() {
        // Load the configuration and extract the database connection
        $config = include __DIR__ . '/../../config/config.php';
        $this->mysqli = $config['db_connection'];
    }

    public function create($name, $columns) {
        // Add base columns
        $columnsSQL = "`id` INT AUTO_INCREMENT PRIMARY KEY, ";
        $columnsSQL .= "`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, ";
        $columnsSQL .= "`user_id` INT NOT NULL, ";
        $columnsSQL .= "`display_order` INT DEFAULT 0, "; // Add display_order column

        // Add user-defined columns
        $columnsSQL .= implode(", ", array_map(
            fn($col, $type) => "`$col` $type",
            array_keys($columns), array_values($columns)
        ));

        $sql = "CREATE TABLE `$name` ($columnsSQL, FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE)"; // SQL query to create the table
        
        // Execute the query and return the result
        if (!$this->mysqli->query($sql)) {
            die('Error creating table: ' . $this->mysqli->error);
        }

        return true;
    }

    public function exists($name) {
        // Ensure the database connection is available
        if (!$this->mysqli) {
            die('Error: No database connection.');
        }
    
        $sql = "SHOW TABLES LIKE '$name'"; // SQL query to check for table existence
        $result = $this->mysqli->query($sql); // Execute the query
    
        if (!$result) { // Check if the query was successful
            die('Query error: ' . $this->mysqli->error);
        }
    
        $exists = $result->num_rows > 0; // Determine if the table exists
        $result->free(); // Free the result set
    
        return $exists;
    }

    public function saveTable($userId, $tableName) {
        // Prepare the SQL query
        $query = "INSERT INTO user_tables (user_id, table_name) VALUES (?, ?)";
        $stmt = $this->mysqli->prepare($query);

        if (!$stmt) {
            error_log("Prepare failed: " . $this->mysqli->error);
            return false;
        }

        $stmt->bind_param('is', $userId, $tableName); // Bind parameters

        $success = $stmt->execute(); // Execute the query

        if (!$success) {
            error_log("Execute failed: " . $stmt->error);
        }

        $stmt->close();

        return $success;
    }

    public function delete($tableName) {
        if (!$this->exists($tableName)) { // Check if the table exists
            return ['success' => false, 'message' => "Table '$tableName' does not exist."];
        }
    
        $this->mysqli->begin_transaction(); // Begin a transaction to ensure data consistency
    
        try {
            // Attempt to drop the table from the database
            $dropTableQuery = "DROP TABLE `$tableName`";
            if (!$this->mysqli->query($dropTableQuery)) {
                throw new Exception("Failed to drop table: " . $this->mysqli->error);
            }
    
            // Remove the table entry from the user_tables table
            $deleteLinkQuery = "DELETE FROM user_tables WHERE table_name = ?";
            $stmt = $this->mysqli->prepare($deleteLinkQuery);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->mysqli->error);
            }
    
            // Bind the table name as a parameter and execute the query
            $stmt->bind_param('s', $tableName);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
    
            $stmt->close(); // Close the prepared statement
    
            $this->mysqli->commit(); // Commit the transaction if all operations succeed
    
            return ['success' => true, 'message' => "Table '$tableName' has been successfully deleted."];
        } catch (Exception $e) {
            $this->mysqli->rollback(); // Rollback the transaction if any operation fails
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }    

    public function getTablesByUser($userId) {
        // SQL query to retrieve the user's tables
        $query = "SELECT id AS table_id, table_name FROM user_tables WHERE user_id = ? ORDER BY table_order ASC";
        $stmt = $this->mysqli->prepare($query);

        if (!$stmt) {
            error_log("Prepare failed: " . $this->mysqli->error);
            return false;
        }

        $stmt->bind_param('i', $userId); // Bind the user ID to the query
        $stmt->execute();
        $result = $stmt->get_result();

        $tables = []; // Array to store table names
        while ($row = $result->fetch_assoc()) {
            $tables[] = [ // Append each table's data (id and name) to the tables array
                'id' => $row['table_id'],
                'name' => $row['table_name']
            ];
        }

        $stmt->close();

        return $tables;
    }

    public function rename($oldName, $newName) {
        if ($this->exists($newName)) { // Check if the new table name already exists
            return ['success' => false, 'message' => "Table '$newName' already exists."];
        }
    
        $sql = "RENAME TABLE `$oldName` TO `$newName`"; // SQL statement to rename the table
    
        if ($this->mysqli->query($sql)) {
            // Update the entry in the user_tables table
            $updateQuery = "UPDATE user_tables SET table_name = ? WHERE table_name = ?";
            $stmt = $this->mysqli->prepare($updateQuery);
            if ($stmt) {
                $stmt->bind_param('ss', $newName, $oldName);
                $stmt->execute();
                $stmt->close();
            }
            return ['success' => true, 'message' => "The table has been successfully renamed from '$oldName' to '$newName'."];
        } else {
            return ['success' => false, 'message' => 'Error renaming table: ' . $this->mysqli->error];
        }
    }    

    public function getColumnType($tableName, $columnName) {
        $sql = "DESCRIBE `$tableName` `$columnName`"; // SQL query to retrieve the column type
        $result = $this->mysqli->query($sql);

        if ($result && $row = $result->fetch_assoc()) {
            return $row['Type']; // Return the data type of the column
        } else {
            return false;
        }
    }

    public function renameColumn($oldName, $newName, $tableName) {
        if (!$this->exists($tableName)) { // Check if the table exists
            return ['success' => false, 'message' => "Table '$tableName' does not exist."];
        }

        $columnType = $this->getColumnType($tableName, $oldName); // Retrieve the data type of the old column
        if ($columnType === false) {
            return ['success' => false, 'message' => "Column '$oldName' does not exist in table '$tableName'."];
        }
    
        $sql = "ALTER TABLE `$tableName` CHANGE COLUMN `$oldName` `$newName` $columnType"; // SQL command to rename the column
    
        if ($this->mysqli->query($sql)) {
            return ['success' => true, 'message' => "The column '$oldName' in the '$tableName' table has been successfully renamed to '$newName'."];
        } else {
            return ['success' => false, 'message' => 'Error renaming column: ' . $this->mysqli->error];
        }
    }    

    public function addColumn($tableName, $columns) {
        if (!$this->exists($tableName)) { // Check if the table exists
            return ['success' => false, 'message' => "Table '$tableName' does not exist."];
        }

        foreach ($columns as $columnName => $columnType) {
            $sql = "ALTER TABLE `$tableName` ADD `$columnName` $columnType"; // SQL query to add columns
            
            if (!$this->mysqli->query($sql)) {
                return ['success' => false, 'message' => 'Error adding column: ' . $this->mysqli->error];
            }
        }

        return ['success' => true, 'message' => 'Columns successfully added to the table.'];
    }

    public function deleteColumn($tableName, $columnName) {
        if (!$this->exists($tableName)) { // Check if the table exists
            return ['success' => false, 'message' => "Table '$tableName' does not exist."];
        }
    
        // Check if the column exists
        $sqlCheckColumn = "SHOW COLUMNS FROM `$tableName` LIKE '$columnName'";
        $result = $this->mysqli->query($sqlCheckColumn);
        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => "Column '$columnName' does not exist in table '$tableName'."];
        }
    
        // Delete the column
        $sql = "ALTER TABLE `$tableName` DROP COLUMN `$columnName`";
        if ($this->mysqli->query($sql)) {
            return ['success' => true, 'message' => "Column '$columnName' has been successfully deleted from table '$tableName'."];
        } else {
            return ['success' => false, 'message' => 'Error deleting column: ' . $this->mysqli->error];
        }
    }    

    public function insertRow($tableName, $data) {
        $columns = array_keys($data);
        $values = array_values($data);

        $columnsList = implode(", ", array_map(fn($col) => "`$col`", $columns));
        $placeholders = implode(", ", array_fill(0, count($values), '?'));

        $sql = "INSERT INTO `$tableName` ($columnsList) VALUES ($placeholders)";
        $stmt = $this->mysqli->prepare($sql);

        if (!$stmt) {
            return ['success' => false, 'message' => $this->mysqli->error];
        }

        $types = '';
        $bindValues = [];
        foreach ($values as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
            $bindValues[] = $value;
        }

        $stmt->bind_param($types, ...$bindValues);

        if ($stmt->execute()) {
            return ['success' => true, 'id' => $stmt->insert_id];
        } else {
            return ['success' => false, 'message' => $stmt->error];
        }
    }

    public function updateRow($tableName, $rowId, $data) {
        $columns = array_keys($data);
        $values = array_values($data);
    
        $setClause = implode(", ", array_map(fn($col) => "`$col` = ?", $columns));
        $sql = "UPDATE `$tableName` SET $setClause WHERE `id` = ?";

        $stmt = $this->mysqli->prepare($sql);
    
        if (!$stmt) {
            return ['success' => false, 'message' => $this->mysqli->error];
        }
    
        $types = '';
        $bindValues = [];
        foreach ($values as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
            $bindValues[] = $value;
        }
    
        $types .= 'i';
        $bindValues[] = $rowId;

        $stmt->bind_param($types, ...$bindValues);
    
        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => $stmt->error];
        }
    }       

    public function deleteRow($tableName, $rowId, $userId) {
        $tableName = $this->mysqli->real_escape_string($tableName);
    
        $sql = "DELETE FROM `$tableName` WHERE `id` = ? AND `user_id` = ?";
        $stmt = $this->mysqli->prepare($sql);
    
        if (!$stmt) {
            return ['success' => false, 'message' => $this->mysqli->error];
        }
    
        $stmt->bind_param('ii', $rowId, $userId);
    
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                return ['success' => true, 'message' => 'Row deleted successfully.'];
            } else {
                return ['success' => false, 'message' => 'No row found or you do not have permission to delete this row.'];
            }
        } else {
            return ['success' => false, 'message' => $stmt->error];
        }
    }    

    public function updateTableOrder($userId, $newOrder) {
        $this->mysqli->begin_transaction();
    
        try {
            foreach ($newOrder as $index => $tableId) {
                $query = "UPDATE user_tables SET table_order = ? WHERE user_id = ? AND id = ?";
                $stmt = $this->mysqli->prepare($query);
    
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $this->mysqli->error);
                }
    
                $stmt->bind_param('iii', $index, $userId, $tableId);
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
    
                $stmt->close();
            }
    
            $this->mysqli->commit();
            return true;
    
        } catch (Exception $e) {
            $this->mysqli->rollback();
            error_log("Error updating table order: " . $e->getMessage());
            return false;
        }
    }  
    
    public function reorderColumns($tableName, $newOrder) {
        $columnMetaQuery = "SHOW COLUMNS FROM `$tableName`";
        $result = $this->mysqli->query($columnMetaQuery);
    
        if (!$result) {
            error_log("Failed to fetch column metadata: " . $this->mysqli->error);
            return false;
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
                error_log("Column $columnName does not exist in table $tableName.");
                return false;
            }
    
            $columnInfo = $columnData[$columnName];
            $afterColumn = $index > 0 ? $newOrder[$index - 1] : null;
    
            $query = $afterColumn
                ? "ALTER TABLE `$tableName` MODIFY COLUMN `$columnName` {$columnInfo['type']} {$columnInfo['null']} {$columnInfo['default']} {$columnInfo['extra']} AFTER `$afterColumn`"
                : "ALTER TABLE `$tableName` MODIFY COLUMN `$columnName` {$columnInfo['type']} {$columnInfo['null']} {$columnInfo['default']} {$columnInfo['extra']} FIRST";
    
            if (!$this->mysqli->query($query)) {
                error_log("Failed to reorder column $columnName: " . $this->mysqli->error);
                return false;
            }
        }
    
        return true;
    }

    public function reorderRows($tableName, $newOrder) {
        $this->mysqli->begin_transaction();
    
        try {
            foreach ($newOrder as $index => $rowId) {
                $query = "UPDATE `$tableName` SET display_order = ? WHERE id = ?";
                $stmt = $this->mysqli->prepare($query);
    
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $this->mysqli->error);
                }
    
                $stmt->bind_param('ii', $index, $rowId);
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
    
                $stmt->close();
            }
    
            $this->mysqli->commit();
            return true;
    
        } catch (Exception $e) {
            $this->mysqli->rollback();
            error_log("Error updating row order: " . $e->getMessage());
            return false;
        }
    }  
}
