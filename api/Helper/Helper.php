<?php

namespace Api\Helper;

use Api\Core\Response;
use Api\Helper\Validator;

class Helper {

    public static function updateOrder($request, $model, $method, $requiredFields) {
        $data = Validator::validateAndExtractRequest($request, $requiredFields);
        if (!is_array($data)) return $data;

        extract($data);

        if (!is_array($order)) {
            Response::error('Invalid order format.');
        }

        if (!method_exists($model, $method)) {
            Response::error("The method $method does not exist in the model $model.");
        }
        
        $result = call_user_func([$model, $method], $tableName ?? $userId, $order);
        
        if (!is_array($result) || !isset($result['success']) || !isset($result['message'])) {
            Response::internalError('Invalid response format from model method.');
        }
        
        match ($result['success']) {
            true => Response::success($result['message']),
            false => Response::internalError($result['message']),
        };
    }

    public static function generateColumnsSQL($columns) {
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
}
