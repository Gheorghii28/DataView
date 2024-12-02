import { getTableData, getTableDataForDeletion, getRowId, getTableName, resetInputs } from './formHelpers.js';
import { createTable, renameTable, deleteTable, renameColumn, addColumn, deleteColumn, deleteRow, } from './tableApi.js';
import { handleFormSubmission, handleOutsideClick, toggleElementPairVisibility } from './utils/domUtils.js';
import { addColumnHandler, addNewColumnHandler, deleteColumnHandler, removeColumnHandler, renameColumnHandler } from './handlers/columnHandlers.js';
import { attachClickHandler } from './utils/eventUtils.js';
import { addColumnButtonHandler, addRowButtonHandler, deleteTableHandler, renameTableHandler, successModalToggleHandler, userDropdownHandler } from './handlers/tableHandlers.js';
import { addDataHandler, deleteRowHandler, renameRowHandler } from './handlers/rowHandlers.js';
import { disableRenameColumnClickListener, disableRenameTableClickListener, getSelectedColumnIds, isRenameColumnClickListenerEnabled, isRenameTableClickListenerEnabled, updateSelectedColumnIds } from './state/clickListenerState.js';
import { getConfigData } from './utils/configUtils.js';
import { selectors } from './selectors.js';

$(document).ready(function () {
    handleFormSubmission('#createTableForm', createTable, () => // "Create Table" form
        getTableData($("input[name='table_name']").val())
    );
    handleFormSubmission('#deleteTableForm', deleteTable, () => // "Delete Table" form
        getTableDataForDeletion()
    );
    handleFormSubmission('#deleteColumnForm', deleteColumn, () => { // "Delete Column" form
        const { baseApiUrl, data } = getTableDataForDeletion();
        data.columnName = $('#deleteColumnConfirmationBtn').data('column-name');
        return { baseApiUrl, data: data };
    });
    handleFormSubmission('#deleteRowForm', deleteRow, () => { // "Delete Row" form
        const { baseApiUrl, userId } = getConfigData();
        const tableName = $('#deleteRowForm').data('table-name');
        const rowId = $('#deleteRowForm').data('row-id');
        const rowData = { name: tableName, userId, rowId };
        return { baseApiUrl, data: rowData };
    });
    handleFormSubmission('#addColumnForm', addColumn, () => { // "Add Column" form
        const tableName = getTableName();
        return getTableData(tableName);
    });

    // Attach Event Handlers
    attachClickHandler(selectors.addColumnButton, addColumnHandler);
    attachClickHandler(selectors.addNewColumnButton, addNewColumnHandler);
    attachClickHandler(selectors.removeColumnButton, removeColumnHandler);
    attachClickHandler(selectors.renameTableButton, renameTableHandler);
    attachClickHandler(selectors.deleteTableButton, deleteTableHandler);
    attachClickHandler(selectors.successModalToggle, successModalToggleHandler);
    attachClickHandler(selectors.userDropdownItem, userDropdownHandler);
    attachClickHandler(selectors.renameColumnButton, renameColumnHandler);
    attachClickHandler(selectors.deleteColumnButton, deleteColumnHandler);
    attachClickHandler(selectors.addColumnButtonDisplay, addColumnButtonHandler);
    attachClickHandler(selectors.addRowButton, addRowButtonHandler);
    attachClickHandler(selectors.addDataButton, addDataHandler);
    attachClickHandler(selectors.deleteRowButton, deleteRowHandler);
    attachClickHandler(selectors.renameRowButton, renameRowHandler);

    $(document).on('dblclick', '#loopRow-new', function () { // Close the inputs row on double-click
        const $row = $('#loopRow-new');
        $row.addClass('hidden');
        resetInputs($row.closest('tr'));
    });
    
    $(document).on('dblclick', '[id^="loopRow-"]', function () { // Close the row on double-click
        const rowId = getRowId(this);
        toggleElementPairVisibility(`row-${rowId}`, `loopRow-${rowId}`);
        resetInputs($(`#loopRow-${rowId}`).closest('tr'));
    });

    $(document).on('click', (e) => {
        handleOutsideClick(e, { // Column Rename Listener
            clickListenerEnabled: isRenameColumnClickListenerEnabled,
            excludeSelector: '.column-header',
            getRenameData: () => {
                const { baseApiUrl, userId } = getConfigData();
                const selectedColumnIds = getSelectedColumnIds();
                return {
                    userId,
                    tableName: getTableName(),
                    oldName: $(`#${selectedColumnIds.columnNameDisplay}`).text().trim(),
                    newName: $(`#${selectedColumnIds.newColumnName}`).val().trim(),
                    baseApiUrl
                };
            },
            onConfirm: (renameData) => {
                const selectedColumnIds = getSelectedColumnIds();
                renameColumn(renameData.baseApiUrl, renameData, selectedColumnIds);
                updateSelectedColumnIds(null);
            },
            onCancel: () => {
                const selectedColumnIds = getSelectedColumnIds();
                toggleElementPairVisibility(selectedColumnIds.columnNameDisplay, selectedColumnIds.columnNameInputWrapper);
                updateSelectedColumnIds(null);
            },
            disableClickListener: disableRenameColumnClickListener
        });
    
        handleOutsideClick(e, { // Table Rename Listener
            clickListenerEnabled: isRenameTableClickListenerEnabled,
            excludeSelector: '#tableNameInputWrapper, #renameTableBtn',
            getRenameData: () => {
                const { baseApiUrl, userId } = getConfigData();
                return {
                    userId,
                    oldName: getTableName(),
                    newName: $('#newTableName').val().trim(),
                    baseApiUrl
                };
            },
            onConfirm: (renameData) => {
                renameTable(renameData.baseApiUrl, renameData);
            },
            onCancel: () => {
                toggleElementPairVisibility('tableNameDisplay', 'tableNameInputWrapper');
            },
            disableClickListener: disableRenameTableClickListener
        });
    });
});
