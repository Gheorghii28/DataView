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
        ];
    ?>
    <?php include 'partials/dropdown_menu.php'; ?> 
<?php endif; ?>
