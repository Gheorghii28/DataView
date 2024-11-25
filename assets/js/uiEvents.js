import { tableColumnTemplate } from './templates.js';
import { getTableData, getConfigData,  focusInputFieldById, toggleElementPairVisibility, getTableDataForDeletion } from './formHelpers.js';
import { createTable, renameTable, deleteTable } from './tableApi.js';

let renameTableClickListenerEnabled = false; // Tracks whether the outside click listener for renaming the table is active

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

$(document).ready(function () {
    $('#createTableForm').on('submit', (e) => { // Handle the "Create Table" form submission
        e.preventDefault();
        
        const { baseApiUrl, tableData } = getTableData(); // Extract API URL and table data from the form

        createTable(baseApiUrl, tableData); // Call the API to create the table
    });

    $(document).on('submit', '#deleteTableForm', (e) => { // Handle the "Delete Table" form submission
        e.preventDefault();
    
        const { baseApiUrl, tableData } = getTableDataForDeletion();

        deleteTable(baseApiUrl, tableData); // Call the API to delete the table
    });    

    $('#addColumnBtn').on('click', (e) => { // Handle adding new columns dynamically
        e.preventDefault();

        $('#columns').append(tableColumnTemplate);
    });

    $(document).on('click', '.remove-column-btn', (e) => { // Handle removing a column dynamically
        e.preventDefault();

        const columnDiv = $(this).closest('.column');
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
            const config = getConfigData(); // Get configuration data (API URL and user ID)
            const baseApiUrl = config.baseApiUrl;
            const userId = config.userId;
            const renameData = {
                userId: userId, // ID of the current user
                oldName: $('#tableNameDisplay').text().replace('Table: ', '').trim(), // Current table name
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
});
