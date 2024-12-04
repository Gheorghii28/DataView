<tr id="row-<?php echo htmlspecialchars($row['id']); ?>" data-row-id="<?php echo htmlspecialchars($row['id']); ?>" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
    <?php foreach ($data['columns'] as $column): ?>
        <?php 
            // Filter out columns you don't want to display
            if (in_array($column['name'], ['id', 'created_at', 'user_id', 'display_order'])) {
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