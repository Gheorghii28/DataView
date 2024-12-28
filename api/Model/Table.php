<?php

namespace Api\Model;

use Api\Core\Response;
use Api\Helper\Helper;
use Exception;
use mysqli;

class Table {

    private $db;
    private $helper;
    private $response;

    public function __construct(mysqli $db) {
        $this->db = $db;
        $this->helper = new Helper();
        $this->response = new Response();
    }

    public function create($name, $columns) {
        if (!$this->db) {
            throw new Exception('Error: No database connection.');
        }

        $columnsSQL = $this->helper->generateColumnsSQL($columns);
        $sql = "CREATE TABLE `$name` ($columnsSQL, FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE)";
        
        if (!$this->db->query($sql)) {
            throw new Exception('Error creating table: ' . $this->db->error);
        }

        return true;
    }

    public function exists($name) {
        $name = $this->db->real_escape_string($name);
        $sql = "SHOW TABLES LIKE '$name'";
        $result = $this->db->query($sql);
    
        if (!$result) {
            throw new Exception('Query error: ' . $this->db->error);
        }
    
        $exists = $result->num_rows > 0;
        $result->free();
    
        return $exists;
    }

    public function saveTable($userId, $tableName) {
        $query = "INSERT INTO user_tables (user_id, table_name) VALUES (?, ?)";
        $stmt = $this->db->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param('is', $userId, $tableName);
        $success = $stmt->execute();

        if (!$success) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
        return $success;
    }

    public function delete($tableName) {
        if (!self::exists($tableName)) {
            return $this->response->errorMessage("Table '$tableName' does not exist.");
        }
    
        $this->db->begin_transaction();
    
        try {
            $dropTableQuery = "DROP TABLE `$tableName`";
            if (!$this->db->query($dropTableQuery)) {
                throw new Exception("Failed to drop table: " . $this->db->error);
            }
    
            $deleteLinkQuery = "DELETE FROM user_tables WHERE table_name = ?";
            $stmt = $this->db->prepare($deleteLinkQuery);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->db->error);
            }
    
            $stmt->bind_param('s', $tableName);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
    
            $stmt->close();
    
            $this->db->commit();
    
            return $this->response->successMessage("Table '$tableName' has been successfully deleted.");
        } catch (Exception $e) {
            $this->db->rollback();
            return $this->response->errorMessage("Error deleting table: " . $e->getMessage());
        }
    }    

    public function getTablesByUser($userId) {
        $query = "SELECT id AS table_id, table_name FROM user_tables WHERE user_id = ? ORDER BY table_order ASC";
        $stmt = $this->db->prepare($query);

        if (!$stmt) {
            return $this->response->errorMessage("Prepare failed: " . $this->db->error);
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

        return $this->response->successMessage('User tables fetched successfully.', $tables, 'tables');
    }

    public function rename($oldName, $newName) {
        if (self::exists($newName)) {
            return $this->response->errorMessage("Table '$newName' already exists.");
        }
    
        $sql = "RENAME TABLE `$oldName` TO `$newName`";
    
        if ($this->db->query($sql)) {
            $updateQuery = "UPDATE user_tables SET table_name = ? WHERE table_name = ?";
            $stmt = $this->db->prepare($updateQuery);
            if ($stmt) {
                $stmt->bind_param('ss', $newName, $oldName);
                $stmt->execute();
                $stmt->close();
            }
            return $this->response->successMessage("The table has been successfully renamed from '$oldName' to '$newName'.");
        } else {
            return $this->response->errorMessage('Error renaming table: ' . $this->db->error);
        }
    } 

    public function updateTableOrder($userId, $newOrder) {
        if (!is_array($newOrder) || empty($newOrder)) {
            return $this->response->errorMessage("Error updating table order: Invalid table order data.");
        }

        $this->db->begin_transaction();
    
        try {
            foreach ($newOrder as $index => $tableId) {
                if (!is_numeric($tableId)) {
                    $this->db->rollback();
                    return $this->response->errorMessage("Error updating table order: Invalid table ID at position $index.");
                }
                
                $query = "UPDATE user_tables SET table_order = ? WHERE user_id = ? AND id = ?";
                $stmt = $this->db->prepare($query);
    
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $this->db->error);
                }
    
                $stmt->bind_param('iii', $index, $userId, $tableId);
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
    
                $stmt->close();
            }
    
            $this->db->commit();
            return $this->response->successMessage("Table order updated successfully.");
    
        } catch (Exception $e) {
            $this->db->rollback();
            return $this->response->errorMessage("Error updating table order: " . $e->getMessage());
        }
    }
}
