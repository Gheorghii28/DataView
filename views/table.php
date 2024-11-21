<?php if (isset($data['error'])): ?>
    <!-- If there is an error, display it -->
    <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert"><?php echo htmlspecialchars($data['error']); ?></div>
<?php else: ?>
    <!-- If no error, display the table -->
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <caption class="text-nowrap p-5 text-lg font-semibold text-left rtl:text-right text-gray-900 bg-white dark:text-white dark:bg-gray-800">
                Table: <?php echo htmlspecialchars($table_name); ?>
            </caption>
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <?php foreach ($data['columns'] as $column): ?>
                        <?php 
                            // Filter out columns you don't want to display
                            if (in_array($column['name'], ['id', 'created_at', 'user_id'])) {
                                continue;
                            }
                        ?>
                        <th scope="col" class="px-6 py-3">
                            <?php echo htmlspecialchars($column['name']); ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['rows'])): ?>
                    <tr>
                        <td colspan="<?php echo count($data['columns']); ?>" class="px-6 py-4 text-center text-gray-500">
                            <div class="p-4 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300" role="alert"><?php echo htmlspecialchars($data['message']); ?></div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['rows'] as $row): ?>
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <?php foreach ($data['columns'] as $column): ?>
                                <?php 
                                    // Filter out columns you don't want to display
                                    if (in_array($column['name'], ['id', 'created_at', 'user_id'])) {
                                        continue;
                                    }
                                ?>
                                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    <?php echo htmlspecialchars($row[$column['name']]); ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
