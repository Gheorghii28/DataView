<?php

namespace Api\Controller;

use Api\Helper\Helper;
use Api\Helper\Validator;
use Api\Model\Row;
use Api\Model\Table;
use Api\Core\Response;

class RowController
{
    
    public static function create($request) {
        $data = Validator::validateAndExtractRequest($request, ['userId', 'tableName', 'data']);
        if (!is_array($data)) return $data;

        extract($data);
        
        $userTables = Table::getTablesByUser($userId);
        $accessCheck = Validator::validateUserTableAccess($userId, $tableName, $userTables);
        if ($accessCheck !== true) return $accessCheck;

        $result = Row::insertRow($tableName, $data);

        match ($result['success']) {
            true => Response::success($result['message'], ['rowId' => $result['id']]),
            false => Response::internalError($result['message'], ['rowId' => $result['id']]),
        };
    }

    public static function update($request) {
        $data = Validator::validateAndExtractRequest($request, ['userId', 'tableName', 'data', 'rowId']);
        if (!is_array($data)) return $data;

        extract($data);
        
        $userTables = Table::getTablesByUser($userId);
        $accessCheck = Validator::validateUserTableAccess($userId, $tableName, $userTables);
        if ($accessCheck !== true) return $accessCheck;

        $result = Row::updateRow($tableName, $rowId, $data);

        match ($result['success']) {
            true => Response::success($result['message']),
            false => Response::internalError($result['message']),
        };
    }

    public static function delete($request) {
        $data = Validator::validateAndExtractRequest($request, ['userId', 'tableName', 'rowId']);
        if (!is_array($data)) return $data;

        extract($data);
    
        $result = Row::deleteRow($tableName, $rowId, $userId);
    
        match ($result['success']) {
            true => Response::success($result['message']),
            false => Response::internalError($result['message']),
        };
    }

    public static function updateRowOrder($request) {
        return Helper::updateOrder($request, Row::class, 'reorderRows', ['userId', 'tableName', 'order']);
    }
}