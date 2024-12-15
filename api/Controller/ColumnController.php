<?php

namespace Api\Controller;

use Api\Helper\Helper;
use Api\Helper\Validator;
use Api\Model\Column;
use Api\Model\Table;
use Api\Core\Response;

class ColumnController
{
    
    public static function create($request) {
        $data = Validator::validateAndExtractRequest($request, ['userId', 'tableName', 'columns']);
        if (!is_array($data)) return $data;

        extract($data);

        $userTables = Table::getTablesByUser($userId);
        $accessCheck = Validator::validateUserTableAccess($userId, $tableName, $userTables);
        if ($accessCheck !== true) return $accessCheck;

        if (!Validator::validateColumns($columns)) {
            Response::error('Invalid columns.');
        }

        $result = Column::addColumn($tableName, $columns);

        match ($result['success']) {
            true => Response::success($result['message']),
            false => Response::internalError($result['message']),
        };
    }

    public static function rename($request) {
        $data = Validator::validateAndExtractRequest($request, ['userId', 'oldName', 'newName', 'tableName']);
        if (!is_array($data)) return $data;

        extract($data);

        if (!Validator::validateColumnName($newName)) {
            Response::error('Invalid new column name. The column name must start with a letter and contain only letters, numbers, and underscores.');
        }

        $userTables = Table::getTablesByUser($userId);
        $accessCheck = Validator::validateUserTableAccess($userId, $tableName, $userTables);
        if ($accessCheck !== true) return $accessCheck;

        $result = Column::renameColumn($oldName, $newName, $tableName);

        match ($result['success']) {
            true => Response::success($result['message'], ['newColumnName' => $newName]),
            false => Response::internalError($result['message']),
        };
    }

    public static function delete($request) {
        $data = Validator::validateAndExtractRequest($request, ['userId', 'tableName', 'columnName']);
        if (!is_array($data)) return $data;

        extract($data);

        $userTables = Table::getTablesByUser($userId);
        $accessCheck = Validator::validateUserTableAccess($userId, $tableName, $userTables);
        if ($accessCheck !== true) return $accessCheck;

        $result = Column::deleteColumn($tableName, $columnName);

        match ($result['success']) {
            true => Response::success($result['message']),
            false => Response::internalError($result['message']),
        };
    }

    public static function updateColumnOrder($request) {
        return Helper::updateOrder($request, Column::class, 'reorderColumns', ['userId', 'tableName', 'order']);
    }
}