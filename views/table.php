<?php if (isset($data['error'])): ?>
    <!-- If there is an error, display it -->
    <?php include 'partials/error_message.php'; ?>
<?php else: ?>
    <!-- If no error, display the table -->
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <?php include 'partials/table_caption.php'; ?>
            <?php include 'partials/table_header.php'; ?>
            <?php include 'partials/table_rows.php'; ?> 
        </table>
    </div>
    <?php 
        $tableActionMenuItems = [ // Define the table action menu items
            ['id' => 'renameTableBtn', 'label' => 'Rename Table'],
            ['id' => 'deleteTableBtn', 'label' => 'Delete Table'],
        ];
        include 'partials/dropdown_menu.php'; 
    ?> 
    <?php 
        $delete_modal_config = [ // Configure the delete confirmation modal
            'delete_button_id' => 'deleteTableConfirmationBtn',
            'modal_id' => 'deleteTableModal',
            'delete_message' => 'Are you sure you want to delete the table "' . htmlspecialchars($table_name) . '"?',
            'table_name' => htmlspecialchars($table_name),
            'form_id' => 'deleteTableForm',
        ];
        include 'partials/modals/delete_confirmation.php';
    ?>
<?php endif; ?>
