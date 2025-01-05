<?php

namespace Api\Helper;

use Api\Core\Response;
use Api\Helper\Validator;

class Helper {

    private $validator;
    private $response;

    public function __construct() {
        $this->validator = new Validator();
        $this->response = new Response();
    }

    public function updateOrder($request, $model, $method, $requiredFields): array|string {
        $validationRequest = $this->validator->validateAndExtractRequest($request, $requiredFields);
        
        if (!$validationRequest['success']) {
            return $this->response->errorMessage('Invalid request format or missing fields.');
        }

        extract($validationRequest['data']);

        if (!is_array($order)) {
            return $this->response->errorMessage('Invalid order format.');
        }

        if (!method_exists($model, $method)) {
            return $this->response->errorMessage("The method $method does not exist in the model " . get_class($model) . ".");
        }
        
        $result = call_user_func([$model, $method], $tableName ?? $userId, $order);
        
        if (!is_array($result) || !isset($result['success']) || !isset($result['message'])) {
            return $this->response->errorMessage('Invalid response format from model method.');
        }
        
        return match ($result['success']) {
            true => $this->response->successMessage($result['message']),
            false => $this->response->errorMessage($result['message']),
        };
    }

    public function generateColumnsSQL($columns): string {
        $columnsSQL = "`id` INT AUTO_INCREMENT PRIMARY KEY, ";
        $columnsSQL .= "`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, ";
        $columnsSQL .= "`user_id` INT NOT NULL, ";
        $columnsSQL .= "`display_order` INT DEFAULT 0, ";
    
        if (!empty($columns)) {
            $columnsSQL .= implode(", ", array_map(
                fn($col, $type) => "`$col` $type",
                array_keys($columns), array_values($columns)
            ));
        }
    
        $columnsSQL = rtrim($columnsSQL, ', ');
    
        return $columnsSQL;
    }
    
    public function existColumnNameInTable($columnName, $tableName, $db): bool {
        $sql = "SHOW COLUMNS FROM `$tableName` LIKE '$columnName'";
        $result = $db->query($sql);

        if ($result && $result->num_rows > 0) {
            return true;
        }

        return false;
    }
}
