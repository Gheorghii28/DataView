import { showSuccessMessage, showErrorMessage } from './notification.js';
import { resetForm, toggleElementPairVisibility, updateElementTextAndValue } from './formHelpers.js';
import { tableListItemTemplate } from './templates.js';

export function createTable(baseApiUrl, tableData) {
    $.ajax({
        url: `${baseApiUrl}/api/table`,
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(tableData),
        success: function (response) {
            if (response.status == 200) {
                resetForm('#formCreateTableModal');
                showSuccessMessage(response.message);
                getUserTables(baseApiUrl, tableData.userId);
            } else {
                showErrorMessage(response.message);
                console.error("Error: " + response.status + " - " + response.message);
            }
        },
        error: function (xhr, status, error) {
            const response = JSON.parse(xhr.responseText);
            const errorMessage = response.message || 'Unknown error';
            showErrorMessage(errorMessage);
            console.error("Error creating the table:", error, "Response:", xhr.responseText);
        }
    });
}

export function getUserTables(baseApiUrl, userId) {
    $.ajax({
        url: `${baseApiUrl}/api/user/tables?userId=${userId}`,
        method: 'GET',
        success: function(response) {
            if (response.status === 200) {
                const tableListContainer = $('#table-list-container');
                tableListContainer.empty();

                response.data.forEach(function(tableName) {
                    const tableItem = tableListItemTemplate(tableName);
                    tableListContainer.append(tableItem);
                });

            } else {
                console.error("Error: " + response.status + " - " + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error geting the user's tables:", error, "Response:", xhr.responseText);
        }
    });
}

export function renameTable(baseApiUrl, renameData) {
    $.ajax({
        url: `${baseApiUrl}/api/renameTable`, // Endpoint for renaming the table
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify(renameData), // Ex: { userId: 1, oldName: 'old_table_name', newName: 'new_table_name' }
        success: function(response) {
            if (response.status === 200) {
                showSuccessMessage(response.message);
                getUserTables(baseApiUrl, renameData.userId); // Update the user's table list
                toggleElementPairVisibility('tableNameDisplay', 'tableNameInputWrapper'); // Hide the input field and show the table name display
                updateElementTextAndValue('tableNameDisplay', 'newTableName', 'oldTableName', 'Table: '); // Update the displayed table name with the new value
            } else {
                showErrorMessage(response.message); // Show error notification for non-200 status
                console.error("Error: " + response.status + " - " + response.message);
            }
        },
        error: function(xhr, status, error) {
            toggleElementPairVisibility('tableNameDisplay', 'tableNameInputWrapper'); // Hide the input field and show the table name display
            const response = JSON.parse(xhr.responseText);
            const errorMessage = response.message || 'Unknown error';
            showErrorMessage(errorMessage);
            console.error("Error renaming the table:", error, "Response:", xhr.responseText);
        }
    });
}
