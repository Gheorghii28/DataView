<tr id="<?php echo 'loopRow-' . htmlspecialchars($row['id'] ?? 'new'); ?>" 
    data-popover-target="popover-row-<?php echo htmlspecialchars($row['id'] ?? 'new'); ?>" 
    data-popover-placement="top" 
    class="hidden bg-white rounded-lg bg-gray-100 dark:bg-gray-900 border-b dark:border-gray-700 <?php echo empty($row) ? 'hidden' : ''; ?>">

    <?php foreach ($data['columns'] as $column): ?>
        <?php 
            // Filter out columns you don't want to display
            if (in_array($column['name'], ['id', 'created_at', 'user_id'])) {
                continue;
            }

            $inputType = 'text';
            $isCheckbox = false;

            switch ($column['type']) {
                case 'int':
                case 'bigint':
                case 'float':
                case 'double':
                    $inputType = 'number';
                    break;
                case 'date':
                    $inputType = 'date';
                    break;
                case 'datetime':
                    $inputType = 'datetime-local';
                    break;
                case 'time':
                    $inputType = 'time';
                    break;
                case 'boolean':
                case 'tinyint':
                    $inputType = 'checkbox';
                    $isCheckbox = true;
                    break;
            }
        ?>
        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
            <div>
                <?php if ($isCheckbox): ?>
                    <input type="checkbox" 
                           name="<?php echo htmlspecialchars($column['name']); ?>" 
                           class="column-input-field block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                           <?php echo !empty($row[$column['name']]) ? 'checked' : ''; ?>
                           data-original="<?php echo !empty($row[$column['name']]) ? 'true' : 'false'; ?>">
                <?php else: ?>
                    <input type="<?php echo htmlspecialchars($inputType); ?>" 
                           name="<?php echo htmlspecialchars($column['name']); ?>" 
                           value="<?php echo htmlspecialchars($row[$column['name']] ?? ''); ?>" 
                           class="column-input-field block w-full p-2 text-gray-900 border border-gray-300 rounded-lg bg-gray-50 text-xs focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                           data-original="<?php echo htmlspecialchars($row[$column['name']] ?? ''); ?>"
                           required>
                <?php endif; ?>
            </div>
        </td>
    <?php endforeach; ?>

    <td class="flex justify-center px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
        <?php if (!empty($row['id'])): ?>
            <button id="addDataBtn-<?php echo htmlspecialchars($row['id']); ?>">
                <svg 
                    class="w-6 h-6 text-blue-700 hover:text-blue-800 focus:ring-4 focus:ring-blue-300 dark:text-blue-600 dark:hover:text-blue-700 focus:outline-none dark:focus:ring-blue-800" 
                    aria-hidden="true" 
                    xmlns="http://www.w3.org/2000/svg" 
                    width="24" 
                    height="24" 
                    fill="none" 
                    viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.651 7.65a7.131 7.131 0 0 0-12.68 3.15M18.001 4v4h-4m-7.652 8.35a7.13 7.13 0 0 0 12.68-3.15M6 20v-4h4"/>
                </svg>
            </button>
        <?php else: ?>
            <button id="addDataBtn-new">
                <svg 
                    class="w-6 h-6 text-blue-700 hover:text-blue-800 focus:ring-4 focus:ring-blue-300 dark:text-blue-600 dark:hover:text-blue-700 focus:outline-none dark:focus:ring-blue-800" 
                    aria-hidden="true" 
                    xmlns="http://www.w3.org/2000/svg" 
                    width="24" 
                    height="24" 
                    fill="currentColor" 
                    viewBox="0 0 24 24">
                    <path fill-rule="evenodd" d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm11-4.243a1 1 0 1 0-2 0V11H7.757a1 1 0 1 0 0 2H11v3.243a1 1 0 1 0 2 0V13h3.243a1 1 0 1 0 0-2H13V7.757Z" clip-rule="evenodd"/>
                </svg>
            </button>
        <?php endif; ?>
    </td>
</tr>
<div 
    data-popover id="popover-row-<?php echo htmlspecialchars($row['id'] ?? 'new'); ?>" 
    role="tooltip" 
    class="absolute z-10 invisible inline-block w-64 text-sm text-gray-500 transition-opacity duration-300 bg-white border border-gray-200 rounded-lg shadow-sm opacity-0 dark:text-gray-400 dark:border-gray-600 dark:bg-gray-800">
    <div class="px-3 py-2 bg-gray-100 border-b border-gray-200 rounded-t-lg dark:border-gray-600 dark:bg-gray-700">
        <h3 class="font-semibold text-gray-900 dark:text-white">Notice</h3>
    </div>
    <div class="px-3 py-2">
        <p>Double-click on the row to close it.</p>
    </div>
    <div data-popper-arrow></div>
</div>
