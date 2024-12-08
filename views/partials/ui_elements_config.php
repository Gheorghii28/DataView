<?php
    $delete_table_modal_config = [ // Configure the delete confirmation modal
        'delete_button_id' => 'deleteTableConfirmationBtn',
        'modal_id' => 'deleteTableModal',
        'delete_message' => 'Are you sure you want to delete the table "' . htmlspecialchars($table_name) . '"?',
        'table_name' => htmlspecialchars($table_name),
        'form_id' => 'deleteTableForm',
    ];

    $delete_column_modal_config = [
        'delete_button_id' => 'deleteColumnConfirmationBtn',
        'modal_id' => 'deleteColumnModal',
        'delete_message' => 'Are you sure you want to delete this column?',
        'table_name' => htmlspecialchars($table_name),
        'column_name' => htmlspecialchars($table_name),
        'form_id' => 'deleteColumnForm',
    ];

    $delete_row_modal_config = [
        'delete_button_id' => 'deleteRowConfirmationBtn',
        'modal_id' => 'deleteRowModal',
        'delete_message' => 'Are you sure you want to delete this row?',
        'table_name' => htmlspecialchars($table_name),
        'form_id' => 'deleteRowForm',
    ];
    
    $tableTriggerId = 'tableTriggerId';
    $tableAriaLabelledby = 'tableAriaLabelledby';
    $tableActionMenuItems = [ // Define the table action menu items
        ['id' => 'renameTableBtn', 'label' => 'Rename Table'],
        ['id' => 'deleteTableBtn', 'label' => 'Delete Table'],
        ['id' => 'viewPdfBtn', 'label' => 'View PDF', 'action' => 'S'],
        ['id' => 'downloadPdfBtn', 'label' => 'Download PDF', 'action' => 'D'],
    ];
    
    $columnTriggerId = 'columnTriggerId';
    $columnAriaLabelledby = 'columnAriaLabelledby';
    $columnActionMenuItems = [ // Define the column action menu items
        ['id' => 'renameColumnBtn', 'label' => 'Rename Column'],
        ['id' => 'addColumnButton', 'label' => 'Add Column'],
        ['id' => 'deleteColumnBtn', 'label' => 'Delete Column'],
        ['id' => 'addRowButton', 'label' => 'Add New Data'],
    ];

    $rowTriggerId = 'rowTriggerId';
    $rowAriaLabelledby = 'rowAriaLabelledby';
    $rowActionMenuItems = [ // Define the row action menu items
        ['id' => 'renameRowBtn', 'label' => 'Edit Row Data'],
        ['id' => 'deleteRowBtn', 'label' => 'Delete Row Data'],
    ];

    function renderDropdownMenu($actionMenuItems, $triggerId, $ariaLabelledby) {
        include 'components/dropdown_menu.php'; 
    }

    function renderDropdownButton($triggerId, $ariaLabelledby) {
        include 'components/dropdown_button.php'; 
    }