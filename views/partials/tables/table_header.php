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
        <th scope="col" class="px-6 py-3">
            <?php renderDropdownButton($columnTriggerId, $columnAriaLabelledby) ?>
        </th>
    </tr>
</thead>
