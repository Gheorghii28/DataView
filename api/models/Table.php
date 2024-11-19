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
}
