<?php

namespace Api\Model;

use Api\Core\DbConnection;
use Exception;
class Table {

    public function __construct() {}

    public static function create($name, $columns) {
        $db = DbConnection::getInstance();

        if (!$db) {
            throw new Exception('Error: No database connection.');
        }

        $columnsSQL = "`id` INT AUTO_INCREMENT PRIMARY KEY, ";
        $columnsSQL .= "`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, ";
        $columnsSQL .= "`user_id` INT NOT NULL, ";
        $columnsSQL .= "`display_order` INT DEFAULT 0, ";

        $columnsSQL .= implode(", ", array_map(
            fn($col, $type) => "`$col` $type",
            array_keys($columns), array_values($columns)
        ));

        $sql = "CREATE TABLE `$name` ($columnsSQL, FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE)";
        
        if (!$db->query($sql)) {
            throw new Exception('Error creating table: ' . $db->error);
        }

        return true;
    }

    public static function exists($name) {
        $db = DbConnection::getInstance();
        $sql = "SHOW TABLES LIKE '$name'";
        $result = $db->query($sql);
    
        if (!$result) {
            throw new Exception('Query error: ' . $db->error);
        }
    
        $exists = $result->num_rows > 0;
        $result->free();
    
        return $exists;
    }

    public static function saveTable($userId, $tableName) {
        $db = DbConnection::getInstance();
        $query = "INSERT INTO user_tables (user_id, table_name) VALUES (?, ?)";
        $stmt = $db->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $db->error);
        }

        $stmt->bind_param('is', $userId, $tableName);
        $success = $stmt->execute();

        if (!$success) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
        return $success;
    }

    public static function delete($tableName) {
        $db = DbConnection::getInstance();

        if (!self::exists($tableName)) {
            return ['success' => false, 'message' => "Table '$tableName' does not exist."];
        }
    
        $db->begin_transaction();
    
        try {
            $dropTableQuery = "DROP TABLE `$tableName`";
            if (!$db->query($dropTableQuery)) {
                throw new Exception("Failed to drop table: " . $db->error);
            }
    
            $deleteLinkQuery = "DELETE FROM user_tables WHERE table_name = ?";
            $stmt = $db->prepare($deleteLinkQuery);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $db->error);
            }
    
            $stmt->bind_param('s', $tableName);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
    
            $stmt->close();
    
            $db->commit();
    
            return ['success' => true, 'message' => "Table '$tableName' has been successfully deleted."];
        } catch (Exception $e) {
            $db->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }    

    public static function getTablesByUser($userId) {
        $db = DbConnection::getInstance();
        $query = "SELECT id AS table_id, table_name FROM user_tables WHERE user_id = ? ORDER BY table_order ASC";
        $stmt = $db->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $db->error);
        }

        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $tables = [];
        while ($row = $result->fetch_assoc()) {
            $tables[] = [
                'id' => $row['table_id'],
                'name' => $row['table_name']
            ];
        }

        $stmt->close();

        return $tables;
    }

    public static function rename($oldName, $newName) {
        $db = DbConnection::getInstance();
        
        if (self::exists($newName)) {
            return ['success' => false, 'message' => "Table '$newName' already exists."];
        }
    
        $sql = "RENAME TABLE `$oldName` TO `$newName`";
    
        if ($db->query($sql)) {
            $updateQuery = "UPDATE user_tables SET table_name = ? WHERE table_name = ?";
            $stmt = $db->prepare($updateQuery);
            if ($stmt) {
                $stmt->bind_param('ss', $newName, $oldName);
                $stmt->execute();
                $stmt->close();
            }
            return ['success' => true, 'message' => "The table has been successfully renamed from '$oldName' to '$newName'."];
        } else {
            return ['success' => false, 'message' => 'Error renaming table: ' . $db->error];
        }
    } 

    public static function updateTableOrder($userId, $newOrder) {
        $db = DbConnection::getInstance();
        $db->begin_transaction();
    
        try {
            foreach ($newOrder as $index => $tableId) {
                $query = "UPDATE user_tables SET table_order = ? WHERE user_id = ? AND id = ?";
                $stmt = $db->prepare($query);
    
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $db->error);
                }
    
                $stmt->bind_param('iii', $index, $userId, $tableId);
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
    
                $stmt->close();
            }
    
            $db->commit();
            return ['success' => true, 'message' => 'Table order updated successfully.'];
    
        } catch (Exception $e) {
            $db->rollback();
            return ['success' => false, 'message' => "Error updating table order: " . $e->getMessage()];
        }
    }
}
