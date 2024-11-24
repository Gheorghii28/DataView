<caption class="text-nowrap p-5 text-lg font-semibold text-left rtl:text-right text-gray-900 bg-white dark:text-white dark:bg-gray-800">
    <form id="renameTableForm">
        <div class="flex justify-between min-w-96">
            <div id="tableNameDisplay">
                Table: <?php echo htmlspecialchars($table_name); ?>
                <input type="text" id="oldTableName" name="oldName" class="hidden mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <div id="tableNameInputWrapper" class="relative hidden">
                <input type="text" id="newTableName" name="newName" value="<?php echo htmlspecialchars($table_name); ?>" class="block px-2.5 pb-1.5 pt-3 w-full text-sm text-gray-900 bg-transparent rounded-lg border-1 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder="Type the new table name" />
                <label for="newTableName" class="absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-3 scale-75 top-1 z-10 origin-[0] bg-white dark:bg-gray-900 px-2 peer-focus:px-2 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:-translate-y-1/2 peer-placeholder-shown:top-1/2 peer-focus:top-1 peer-focus:scale-75 peer-focus:-translate-y-3 start-1 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto">Table Name</label>
            </div>
            <?php renderDropdownButton($tableTriggerId, $tableAriaLabelledby) ?>
        </div>
    </form>
</caption>