<?php if (isset($data['error'])): ?>
    <!-- If there is an error, display it -->
    <?php include 'partials/view_error_message.php'; ?>
<?php else: ?>
    <!-- If no error, display the table -->
    <?php
        include __DIR__ . '/partials/ui_elements_config.php'; // Include configuration and functions for UI elements
    ?>
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <?php include 'partials/tables/table_caption.php'; ?>
            <?php include 'partials/tables/table_header.php'; ?>
            <?php include 'partials/tables/table_rows.php'; ?> 
        </table>
    </div>
    <?php
        renderDropdownMenu($tableActionMenuItems, $tableTriggerId, $tableAriaLabelledby);
        renderDropdownMenu($columnActionMenuItems, $columnTriggerId, $columnAriaLabelledby);
    ?> 
    <?php 
        $delete_modal_config = $delete_table_modal_config;
        include 'partials/modals/delete_confirmation.php';
        
        $delete_modal_config = $delete_column_modal_config;
        include 'partials/modals/delete_confirmation.php';
        include 'partials/modals/forms/form_add_column.php';
    ?>
<?php endif; ?>
