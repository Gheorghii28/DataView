<?php

namespace Api\Model;

use Api\Helper\Validator;
use Exception;
use mysqli;
use mysqli_sql_exception;

class Row {

    private $db;
    private $validator;

    public function __construct(mysqli $db) {
        $this->db = $db;
        $this->validator = new Validator();
    }

    public function getRows($tableName, $userId) {
        $sql = "SELECT * FROM `$tableName` WHERE user_id = ? ORDER BY display_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }

    public function insertRow($tableName, $data) {
        $columns = array_keys($data);
        $values = array_values($data);

        $columnsList = implode(", ", array_map(fn($col) => "`$col`", $columns));
        $placeholders = implode(", ", array_fill(0, count($values), '?'));

        try {
            $sql = "INSERT INTO `$tableName` ($columnsList) VALUES ($placeholders)";
            $stmt = $this->db->prepare($sql);
    
            if (!$stmt) {
                return ['success' => false, 'message' => "MySQL Error (prepare): " . $this->db->error];
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
                return ['success' => false, 'message' => "MySQL Error (execute): " . $stmt->error];
            }
        } catch (mysqli_sql_exception $e) {
            return ['success' => false, 'message' => "MySQL Exception: " . $e->getMessage()];
        }
    }

    public function updateRow($tableName, $rowId, $data) {
        $columns = array_keys($data);
        $values = array_values($data);
        $setClause = implode(", ", array_map(fn($col) => "`$col` = ?", $columns));

        try {
            $sql = "UPDATE `$tableName` SET $setClause WHERE `id` = ?";
            $stmt = $this->db->prepare($sql);
        
            if (!$stmt) {
                return ['success' => false, 'message' => $this->db->error];
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
        } catch (mysqli_sql_exception $e) {
            return ['success' => false, 'message' => "MySQL Exception: " . $e->getMessage()];
        }
    }

    public function deleteRow($tableName, $rowId, $userId) {
        $tableName = $this->db->real_escape_string($tableName);
    
        $sql = "DELETE FROM `$tableName` WHERE `id` = ? AND `user_id` = ?";
        $stmt = $this->db->prepare($sql);
    
        if (!$stmt) {
            return ['success' => false, 'message' => $this->db->error];
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

    public function reorderRows($tableName, $newOrder) {
        $this->db->begin_transaction();
    
        try {
            $resultValidateRowIds = $this->validator->validateRowIds($this->db, $tableName, $newOrder);

            if (!$resultValidateRowIds['success']) {
                return ['success' => false, 'message' => "Error validating row IDs: " . $resultValidateRowIds['message']];
            }

            foreach ($newOrder as $index => $rowId) {
                $query = "UPDATE `$tableName` SET display_order = ? WHERE id = ?";
                $stmt = $this->db->prepare($query);
    
                if (!$stmt) {
                    return ['success' => false, 'message' => "Prepare failed: " . $this->db->error];
                }
    
                $stmt->bind_param('ii', $index, $rowId);
                if (!$stmt->execute()) {
                    return ['success' => false, 'message' => "Execute failed: " . $stmt->error];
                }
    
                $stmt->close();
            }
    
            $this->db->commit();
            return ['success' => true, 'message' => 'Row order updated successfully.'];
    
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => "Error updating row order: " . $e->getMessage()];
        }
    }  
}