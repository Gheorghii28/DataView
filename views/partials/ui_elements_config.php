<?php
    $delete_modal_config = [ // Configure the delete confirmation modal
        'delete_button_id' => 'deleteTableConfirmationBtn',
        'modal_id' => 'deleteTableModal',
        'delete_message' => 'Are you sure you want to delete the table "' . htmlspecialchars($table_name) . '"?',
        'table_name' => htmlspecialchars($table_name),
        'form_id' => 'deleteTableForm',
    ];
    
    $tableTriggerId = 'tableTriggerId';
    $tableAriaLabelledby = 'tableAriaLabelledby';
    $tableActionMenuItems = [ // Define the table action menu items
        ['id' => 'renameTableBtn', 'label' => 'Rename Table'],
        ['id' => 'deleteTableBtn', 'label' => 'Delete Table'],
    ];
    
    $columnTriggerId = 'columnTriggerId';
    $columnAriaLabelledby = 'columnAriaLabelledby';
    $columnActionMenuItems = [ // Define the column action menu items
        ['id' => 'renameColumnBtn', 'label' => 'Rename Column'],
        ['id' => 'deleteColumnBtn', 'label' => 'Delete Column'],
    ];

    function renderDropdownMenu($actionMenuItems, $triggerId, $ariaLabelledby) {
        include 'components/dropdown_menu.php'; 
    }

    function renderDropdownButton($triggerId, $ariaLabelledby) {
        include 'components/dropdown_button.php'; 
    }