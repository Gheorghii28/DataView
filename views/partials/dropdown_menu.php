<?php if (isset($tableActionMenuItems) && is_array($tableActionMenuItems)): ?>
    <div id="dropdownDots" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-40 dark:bg-gray-700 dark:divide-gray-600">
        <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownMenuIconButton">
            <?php foreach ($tableActionMenuItems as $item): ?>
                <li id="<?php echo htmlspecialchars($item['id']); ?>">
                    <button class="w-full text-left block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                        <?php echo htmlspecialchars($item['label']); ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
