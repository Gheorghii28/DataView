<tbody>
    <?php include 'row_input_template.php'; ?>
    <?php if (empty($data['rows'])): ?>
        <tr>
            <td colspan="<?php echo count($data['columns']); ?>" class="px-6 py-4 text-center text-gray-500">
                <div class="p-4 mb-4 text-sm text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300" role="alert"><?php echo htmlspecialchars($data['message']); ?></div>
            </td>
        </tr>
    <?php else: ?>
        <?php foreach ($data['rows'] as $row): ?>
            <?php include 'row_input_template.php'; ?>
            <tr id="row-<?php echo htmlspecialchars($row['id']); ?>" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
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
                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    <?php 
                        $triggerId = $rowTriggerId . htmlspecialchars($row['id']);
                        $ariaLabelledby = $rowAriaLabelledby . htmlspecialchars($row['id']);
                        $dynamicMenuItems = array_map(function($item) use ($row) {
                            $item['id'] = $item['id'] . '-' . htmlspecialchars($row['id']);
                            return $item;
                        }, $rowActionMenuItems);
                        renderDropdownButton($triggerId, $ariaLabelledby);
                        renderDropdownMenu($dynamicMenuItems, $triggerId, $ariaLabelledby);
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</tbody>