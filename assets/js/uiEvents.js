import { tableColumnTemplate } from './templates.js';
import { getTableData, getConfigData,  focusInputFieldById, toggleElementPairVisibility, getTableDataForDeletion, getRowData, getRowId, getTableName } from './formHelpers.js';
import { createTable, renameTable, deleteTable, renameColumn, addColumn, deleteColumn, saveRowData, deleteRow, updateRowData } from './tableApi.js';
import { interactiveColumnHighlightClass, interactiveDeleteHighlightClass } from './constants.js';

let renameTableClickListenerEnabled = false; // Tracks whether the outside click listener for renaming the table is active
let renameColumnClickListenerEnabled = false; // Tracks whether the outside click listener for renaming the column is active
let selectedColumnIds = null; // Column IDs based on the column name (this is the currently selected column)

function enableRenameTableClickListener() { // Function to enable the outside click listener for table renaming
    if (!renameTableClickListenerEnabled) {
        renameTableClickListenerEnabled = true;
    }
}

function disableRenameTableClickListener() { // Function to disable the outside click listener for table renaming
    if (renameTableClickListenerEnabled) {
        renameTableClickListenerEnabled = false;
    }
}

function enableRenameColumnClickListener() { // Function to enable the outside click listener for column renaming
    if (!renameColumnClickListenerEnabled) {
        renameColumnClickListenerEnabled = true;
    }
}

function disableRenameColumnClickListener() { // Function to disable the outside click listener for column renaming
    if (renameColumnClickListenerEnabled) {
        renameColumnClickListenerEnabled = false;
    }
}

function updateSelectedColumnIds(newIds) {
    selectedColumnIds = newIds;
}

$(document).ready(function () {
    $('#createTableForm').on('submit', (e) => { // Handle the "Create Table" form submission
        e.preventDefault();
        
        const { baseApiUrl, tableData } = getTableData($("input[name='table_name']").val()); // Extract API URL and table data from the form

        createTable(baseApiUrl, tableData); // Call the API to create the table
    });

    $(document).on('submit', '#deleteTableForm', (e) => { // Handle the "Delete Table" form submission
        e.preventDefault();
    
        const { baseApiUrl, tableData } = getTableDataForDeletion();

        deleteTable(baseApiUrl, tableData); // Call the API to delete the table
    });    

    $(document).on('submit', '#deleteColumnForm', (e) => { // Handle the "Delete Table" form submission
        e.preventDefault();

        const { baseApiUrl, tableData } = getTableDataForDeletion();
        const columnName = $('#deleteColumnConfirmationBtn').data('column-name'); 
        tableData.columnName = columnName;

        deleteColumn(baseApiUrl, tableData); // Call the API to delete the column
    });    
    
    $(document).on('submit', '#deleteRowForm', (e) => { // Handle the "Delete Row" form submission
        e.preventDefault();

        const { baseApiUrl, userId } = getConfigData(); // Get configuration data (API URL and user ID)
        const tableName = $('#deleteRowForm').data('table-name'); 
        const rowId = $('#deleteRowForm').data('row-id'); 
        const rowData = { name: tableName, userId: userId, rowId: rowId }

        deleteRow(baseApiUrl, rowData); // Call the API to delete the row
    });    

    $(document).on('submit', '#addColumnForm', (e) => { // Handle the "Add Column" form submission
        e.preventDefault();

        const tableName = getTableName();
        const { baseApiUrl, tableData } = getTableData(tableName); // Extract API URL and table data from the form
        addColumn(baseApiUrl, tableData);
    });

    $('#addColumnBtn').on('click', (e) => { // Handle adding new columns dynamically
        e.preventDefault();

        $('#columns').append(tableColumnTemplate);
    });

    $(document).on('click', '#addNewColumnBtn', (e) => { // Add a new column input field dynamically when the "Add Another Column" button is clicked
        e.preventDefault();

        $('#addColumns').append(tableColumnTemplate); // Append the column input template to the form
    });

    $(document).on('click', '.remove-column-btn', (e) => { // Handle removing a column dynamically
        e.preventDefault();

        const columnDiv = $(e.target).closest('.column');
        columnDiv.remove();
    });

    $(document).on('click', '#renameTableBtn', (e) => { // Handle the "Rename Table" button click
        e.preventDefault();
        toggleElementPairVisibility('tableNameInputWrapper', 'tableNameDisplay'); // Hide the table name display and show the input field for renaming
        focusInputFieldById('newTableName'); // Focus the input field for renaming
        enableRenameTableClickListener(); // Enable the outside click listener to detect clicks outside the input field
    });

    $(document).on('click', '#deleteTableBtn', (e) => { // Handle the "Delete Table" button click
        e.preventDefault();
        $('#deleteTableConfirmationBtn').click(); // Trigger the click event to show the delete confirmation modal
    });

    $(document).on('click', (e) => { // Handle clicks outside the input field to confirm renaming
        if (renameTableClickListenerEnabled && !$(e.target).closest('#tableNameInputWrapper, #renameTableBtn').length) { // Check if the click happened outside the input field and the rename button
            const { baseApiUrl, userId } = getConfigData(); // Get configuration data (API URL and user ID)
            const tableName = getTableName();
            const renameData = {
                userId: userId, // ID of the current user
                oldName: tableName, // Current table name
                newName: $('#newTableName').val().trim()  // New table name entered by the user
            };
            if(renameData.oldName !== renameData.newName) {
                renameTable(baseApiUrl, renameData); // Call the API to rename the table
            } else {
                toggleElementPairVisibility('tableNameDisplay', 'tableNameInputWrapper'); // Hide the input field and show the table name display
            }
            disableRenameTableClickListener(); // Disable the outside click listener
        }
    });

    $('[data-modal-toggle="successModal"]').on('click', function() { // Handles click on button with data-modal-toggle="successModal". 
        if (window.loadViewAllowed) { // If window.loadViewAllowed is true, it calls the loadView function to load the 'dashboard' view into 'view-container'.
            loadView('view-container', 'dashboard');
        }
    });

    $('#user-dropdown li').on('click', function () { // Hides the user dropdown menu when an option is selected
        $('#user-dropdown').addClass('hidden');
    });

    $(document).on('click', '#renameColumnBtn', function () { // Handle the "Rename Column" button click
        $('.column-header').addClass(interactiveColumnHighlightClass); // Add the interactive highlight class to all column headers

        $('.column-header').on('click', function () { // Allow user to select a column to rename
            updateSelectedColumnIds({
                columnNameInputWrapper: $(this).data('input-wrapper-id'),
                columnNameDisplay: $(this).data('column-display-id'),
                newColumnName: $(this).data('new-name-input-id'),
                oldColumnName: $(this).data('old-name-input-id'),
            });
            toggleElementPairVisibility(selectedColumnIds.columnNameInputWrapper, selectedColumnIds.columnNameDisplay); // Show input field and hide the display text
            focusInputFieldById(selectedColumnIds.newColumnName); // Focus on the new column name input field
            enableRenameColumnClickListener(); // Enable the outside click listener to detect clicks outside the input field
            
            $('.column-header').removeClass(interactiveColumnHighlightClass); // Remove the interactive highlight class from all column headers
            $('.column-header').off('click'); // Remove the click event listener from all column headers
        });
    });

    $(document).on('click', '#deleteColumnBtn', function () { // Handle the "Delete Column" button click
        $('#columnTriggerId').addClass('hidden');
        $('.column-header').addClass(interactiveDeleteHighlightClass); // Add the interactive highlight class to all column headers

        $('.column-header').on('click', function () { // Allow user to select a column to rename
            const columnName = $(this).data('column-name'); 
            $('#deleteColumnConfirmationBtn').attr('data-column-name', columnName);
            $('#deleteColumnConfirmationBtn').click(); // Trigger the click event to show the delete confirmation modal
            $('.column-header').removeClass(interactiveDeleteHighlightClass); // Remove the interactive highlight class from all column headers
            $('.column-header').off('click'); // Remove the click event listener from all column headers
        });
    });

    $(document).on('click', '#addColumnButton', function () { // Handle the "Add Column" button click
        $('#columnTriggerId').addClass('hidden');
        $('#addColumnModalButton').click();
    });

    $(document).on('dblclick', '#loopRow-new', function () { // Close the inputs row on double-click
        $('#loopRow-new').addClass('hidden');

        $('#loopRow-new').closest('tr').find('input').each(function() { // Reset input fields
            if ($(this).attr('type') === 'checkbox') {
                $(this).prop('checked', false); // Reset checkboxes
            } else {
                $(this).val(''); // Clear text fields and other input types
            }
        });
    });

    $(document).on('click', '#addRowButton', function () { // Handle the "Add New Data" button click
        toggleElementPairVisibility('loopRow-new', 'columnTriggerId');
        $('.column-input-field').first().focus();
    });

    $(document).on('click', '[id^="addDataBtn-"]', function () { // Handle click to confirm row adding
        const rowId = getRowId(this);
        const inputsData = {};
        let isValid = true;

        $(`#loopRow-${rowId}`).closest('tr').find('input').each(function() { // Iterate through each input field in the current row
            const inputName = $(this).attr('name');
            let inputValue = $(this).val();

            if (!inputValue && $(this).attr('type') !== 'checkbox') { // Check if the field is empty
                $(this).focus(); // Focus the empty input field
                isValid = false;
                return false;
            }

            if ($(this).attr('type') === 'checkbox') { // Handle checkboxes to store true/false values
                inputValue = $(this).prop('checked') ? 1 : 0;
            }

            inputsData[inputName] = inputValue; // Save the data to the inputsData object
        });

        if (!isValid) {
            return; // Abort processing if a field is empty
        }
        const tableName = getTableName();
        const { baseApiUrl, rowData } = getRowData(tableName, inputsData); // Get base API URL and prepare row data
        
        if (rowId === 'new') {
            saveRowData(baseApiUrl, rowData);
        } else if (Number.isInteger(parseInt(rowId))) {
            rowData.rowId = rowId;
            updateRowData(baseApiUrl, rowData);
        }
    });

    $(document).on('click', '[id^="deleteRowBtn-"]', function () { // Handle row deletion button click
        const rowId = getRowId(this);

        $('#deleteRowConfirmationBtn').click(); // Trigger the click event to show the delete confirmation modal
        $('#deleteRowForm').attr('data-row-id', rowId);
    });    

    $(document).on('click', '[id^="renameRowBtn-"]', function () { // Handle row rename button click
        const rowId = getRowId(this);
        const inputField = $(`#loopRow-${rowId} .column-input-field`).first();

        inputField.focus()[0].setSelectionRange(inputField.val().length, inputField.val().length);
        $(`#rowTriggerId${rowId}`).addClass('hidden');
        toggleElementPairVisibility(`loopRow-${rowId}`, `row-${rowId}`);
    });
    
    $(document).on('dblclick', '[id^="loopRow-"]', function () { // Close the row on double-click
        const rowId = getRowId(this);
        toggleElementPairVisibility(`row-${rowId}`, `loopRow-${rowId}`);

        $(`#loopRow-${rowId}`).closest('tr').find('input').each(function() { // Reset input fields
            if ($(this).attr('type') === 'checkbox') {
                // Reset checkbox to its original state
                const originalChecked = $(this).attr('data-original') === 'true';
                $(this).prop('checked', originalChecked);
            } else {
                // Reset the value for other input types
                const originalValue = $(this).attr('data-original');
                $(this).val(originalValue);
            }
        });
    });

    $(document).on('click', (e) => { // Handle clicks outside the input field to confirm column renaming
        if (renameColumnClickListenerEnabled && selectedColumnIds !== null) {

            if (!$(e.target).closest('.column-header').length) { // Check if the click happened outside the input field
                const { baseApiUrl, userId } = getConfigData(); // Get configuration data (API URL and user ID)
                const tableName = getTableName();
                const renameData = {
                    userId: userId, // ID of the current user
                    tableName: tableName, // Current table name
                    oldName: $(`#${selectedColumnIds.columnNameDisplay}`).text().trim(), // Current column name
                    newName: $(`#${selectedColumnIds.newColumnName}`).val().trim()  // New column name entered by the user
                };
                
                if (renameData.oldName !== renameData.newName) {
                    renameColumn(baseApiUrl, renameData, selectedColumnIds); // Call the API to rename the column
                } else {
                    toggleElementPairVisibility(selectedColumnIds.columnNameDisplay, selectedColumnIds.columnNameInputWrapper); // Hide the input field and show the column name display if the names are the same
                }

                updateSelectedColumnIds(null);
                disableRenameColumnClickListener(); // Disable the outside click listener after the action is completed
            }
        }
    });
});
