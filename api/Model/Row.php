<?php

namespace Api\Model;

use Api\Core\DbConnection;
use Exception;

class Row {

    public function __construct() {}

    public static function getRows($tableName, $userId) {
        $db = DbConnection::getInstance();
        $sql = "SELECT * FROM `$tableName` WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }

    public static function insertRow($tableName, $data) {
        $db = DbConnection::getInstance();
        $columns = array_keys($data);
        $values = array_values($data);

        $columnsList = implode(", ", array_map(fn($col) => "`$col`", $columns));
        $placeholders = implode(", ", array_fill(0, count($values), '?'));

        $sql = "INSERT INTO `$tableName` ($columnsList) VALUES ($placeholders)";
        $stmt = $db->prepare($sql);

        if (!$stmt) {
            return ['success' => false, 'message' => $db->error];
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
            return ['success' => true, 'message' => 'Row successfully added.', 'id' => $stmt->insert_id];
        } else {
            return ['success' => false, 'message' => $stmt->error];
        }
    }

    public static function updateRow($tableName, $rowId, $data) {
        $db = DbConnection::getInstance();
        $columns = array_keys($data);
        $values = array_values($data);
    
        $setClause = implode(", ", array_map(fn($col) => "`$col` = ?", $columns));
        $sql = "UPDATE `$tableName` SET $setClause WHERE `id` = ?";

        $stmt = $db->prepare($sql);
    
        if (!$stmt) {
            return ['success' => false, 'message' => $db->error];
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
            return ['success' => true, 'message' => 'Row successfully updated.'];
        } else {
            return ['success' => false, 'message' => $stmt->error];
        }
    }

    public static function deleteRow($tableName, $rowId, $userId) {
        $db = DbConnection::getInstance();
        $tableName = $db->real_escape_string($tableName);
    
        $sql = "DELETE FROM `$tableName` WHERE `id` = ? AND `user_id` = ?";
        $stmt = $db->prepare($sql);
    
        if (!$stmt) {
            return ['success' => false, 'message' => $db->error];
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

    public static function reorderRows($tableName, $newOrder) {
        $db = DbConnection::getInstance();
        $db->begin_transaction();
    
        try {
            foreach ($newOrder as $index => $rowId) {
                $query = "UPDATE `$tableName` SET display_order = ? WHERE id = ?";
                $stmt = $db->prepare($query);
    
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $db->error);
                }
    
                $stmt->bind_param('ii', $index, $rowId);
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
    
                $stmt->close();
            }
    
            $db->commit();
            return ['success' => true, 'message' => 'Row order updated successfully.'];
    
        } catch (Exception $e) {
            $db->rollback();
            return ['success' => false, 'message' => "Error updating row order: " . $e->getMessage()];
        }
    }  
}