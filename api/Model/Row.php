<?php

namespace Api\Model;

use Api\Core\Response;
use Api\Helper\Validator;
use Exception;
use mysqli;
use mysqli_sql_exception;

class Row {

    private $db;
    private $validator;
    private $response;

    public function __construct(mysqli $db) {
        $this->db = $db;
        $this->validator = new Validator();
        $this->response = new Response();
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
                return $this->response->errorMessage("MySQL Error (prepare): " . $this->db->error);
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
                return $this->response->successMessage('Row successfully added.', [$stmt->insert_id], 'rowId');
            } else {
                return $this->response->errorMessage("MySQL Error (execute): " . $stmt->error);
            }
        } catch (mysqli_sql_exception $e) {
            return $this->response->errorMessage("MySQL Exception: " . $e->getMessage());
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
                return $this->response->errorMessage("MySQL Error (prepare): " . $this->db->error);
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
                return $this->response->successMessage('Row successfully updated.');
            } else {
                return $this->response->errorMessage("MySQL Error (execute): " . $stmt->error);
            }
        } catch (mysqli_sql_exception $e) {
            return $this->response->errorMessage("MySQL Exception: " . $e->getMessage());
        }
    }

    public function deleteRow($tableName, $rowId, $userId) {
        $tableName = $this->db->real_escape_string($tableName);
    
        $sql = "DELETE FROM `$tableName` WHERE `id` = ? AND `user_id` = ?";
        $stmt = $this->db->prepare($sql);
    
        if (!$stmt) {
            return $this->response->errorMessage("Prepare failed: " . $this->db->error);
        }
    
        $stmt->bind_param('ii', $rowId, $userId);
    
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                return $this->response->successMessage('Row deleted successfully.');
            } else {
                return $this->response->errorMessage('No row found or you do not have permission to delete this row.');
            }
        } else {
            return $this->response->errorMessage('Error deleting row: ' . $stmt->error);
        }
    }

    public function reorderRows($tableName, $newOrder) {
        $this->db->begin_transaction();
    
        try {
            $resultValidateRowIds = $this->validator->validateRowIds($this->db, $tableName, $newOrder);

            if (!$resultValidateRowIds['success']) {
                return $this->response->errorMessage("Error validating row IDs: " . $resultValidateRowIds['message']);
            }

            foreach ($newOrder as $index => $rowId) {
                $query = "UPDATE `$tableName` SET display_order = ? WHERE id = ?";
                $stmt = $this->db->prepare($query);
    
                if (!$stmt) {
                    return $this->response->errorMessage("Prepare failed: " . $this->db->error);
                }
    
                $stmt->bind_param('ii', $index, $rowId);
                if (!$stmt->execute()) {
                    return $this->response->errorMessage("Execute failed: " . $stmt->error);
                }
    
                $stmt->close();
            }
    
            $this->db->commit();
            return $this->response->successMessage('Row order updated successfully.');
    
        } catch (Exception $e) {
            $this->db->rollback();
            return $this->response->errorMessage("Error updating row order: " . $e->getMessage());
        }
    }  
}