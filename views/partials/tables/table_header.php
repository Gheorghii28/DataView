<thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
    <tr id="column-list-container">
        <?php foreach ($data['columns'] as $index => $column): ?>
            <?php 
                // Filter out columns you don't want to display
                if (in_array($column['name'], ['id', 'created_at', 'user_id', 'display_order'])) {
                    continue;
                }
                
                $columnName = htmlspecialchars($column['name']);
            ?>
            <th scope="col" class="column-header px-6 py-3" 
                data-column-name="<?php echo $columnName;?>"
                data-input-wrapper-id="columnNameInputWrapper-<?php echo $index; ?>"
                data-column-display-id="columnNameDisplay-<?php echo $index; ?>"
                data-new-name-input-id="newColumnName-<?php echo $index; ?>"
                data-old-name-input-id="oldColumnName-<?php echo $index; ?>"
            >
                <div id="columnNameDisplay-<?php echo $index; ?>">
                    <?php echo $columnName; ?>
                    <input type="text" id="oldColumnName-<?php echo $index; ?>" name="oldName" class="hidden mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div id="columnNameInputWrapper-<?php echo $index; ?>" class="relative hidden">
                    <input type="text" id="newColumnName-<?php echo $index; ?>" name="newName" value="<?php echo $columnName; ?>" class="block px-2.5 pb-1.5 pt-3 w-full text-sm text-gray-900 bg-transparent rounded-lg border-1 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder="Type the new column name" />
                    <label for="newColumnName-<?php echo $index; ?>" class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-3 scale-75 top-1 z-10 origin-[0] bg-white dark:bg-gray-900 px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-1 peer-focus:scale-75 peer-focus:-translate-y-3 start-1 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto">Column Name</label>
                </div>
            </th>
        <?php endforeach; ?>
        <th scope="col" class="px-6 py-3">
            <?php renderDropdownButton($columnTriggerId, $columnAriaLabelledby) ?>
        </th>
    </tr>
</thead>
