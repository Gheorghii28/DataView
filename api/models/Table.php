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
        $query = "SELECT table_name FROM user_tables WHERE user_id = ?";
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
            $tables[] = $row['table_name'];
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
}
