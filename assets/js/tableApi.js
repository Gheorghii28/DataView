import { showSuccessMessage, showErrorMessage } from './notification.js';
import { resetForm, toggleElementPairVisibility, updateDeleteConfirmation, updateElementTextAndValue } from './formHelpers.js';
import { tableListItemTemplate } from './templates.js';
import { loadView } from './viewLoader.js';

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

export function deleteTable(baseApiUrl, tableData) {
    $.ajax({
        url: `${baseApiUrl}/api/table`,
        method: 'DELETE',
        contentType: 'application/json',
        data: JSON.stringify(tableData), // Ex: { userId: 1, tableName: 'table_name' }
        success: function (response) {
            if (response.status == 200) {
                showSuccessMessage(response.message, true);
                getUserTables(baseApiUrl, tableData.userId); // Update the user's table list
            } else {
                showErrorMessage(response.message); // Show error notification for non-200 status
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
                updateDeleteConfirmation( // Updates the delete confirmation modal with the new table name and message
                    '#deleteTableForm', 
                    `Are you sure you want to delete the table  "${response.data.newTableName}" ?`, 
                    response.data.newTableName, 
                    'table-name'
                );
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

export function renameColumn(baseApiUrl, renameData, ids) {
    $.ajax({
        url: `${baseApiUrl}/api/column`, // Endpoint for renaming the column
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify(renameData), // Ex: { userId: 1, tableName: 'current_table_name', oldName: 'old_column_name', newName: 'new_column_name' }
        success: function(response) {
            if (response.status === 200) {
                showSuccessMessage(response.message);
                toggleElementPairVisibility(ids.columnNameDisplay, ids.columnNameInputWrapper); // Hide the input field and show the column name display
                updateElementTextAndValue(ids.columnNameDisplay, ids.newColumnName, ids.oldColumnName); // Update the displayed column name with the new value
                loadView('view-container', 'table', renameData.tableName);
            } else {
                showErrorMessage(response.message); // Show error notification for non-200 status
                console.error("Error: " + response.status + " - " + response.message);
            }
        },
        error: function(xhr, status, error) {
            toggleElementPairVisibility(ids.columnNameDisplay, ids.columnNameInputWrapper); // Hide the input field and show the column name display
            const response = JSON.parse(xhr.responseText);
            const errorMessage = response.message || 'Unknown error';
            showErrorMessage(errorMessage);
            console.error("Error renaming the column:", error, "Response:", xhr.responseText);
        }
    });
}

export function addColumn(baseApiUrl, tableData) {
    $.ajax({
        url: `${baseApiUrl}/api/column`,
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(tableData),
        success: function(response) {
            if (response.status === 200) {
                resetForm('#formAddColumnModal');
                showSuccessMessage(response.message);
                loadView('view-container', 'table', tableData.name);
            } else {
                showErrorMessage(response.message);
                console.error("Error: " + response.status + " - " + response.message);
            }
        },
        error: function(xhr, status, error) {
            const response = JSON.parse(xhr.responseText);
            const errorMessage = response.message || 'Unknown error';
            showErrorMessage(errorMessage);
            console.error("Error adding the column:", error, "Response:", xhr.responseText);
        }
    });
}

export function deleteColumn(baseApiUrl, tableData) {
    $.ajax({
        url: `${baseApiUrl}/api/column`,
        method: 'DELETE',
        contentType: 'application/json',
        data: JSON.stringify(tableData), // Ex: { "userId": 1, "tableName": "example_table", "columnName": "age" }
        success: function(response) {
            if (response.status === 200) {
                showSuccessMessage(response.message);
                loadView('view-container', 'table', tableData.tableName);
            } else {
                showErrorMessage(response.message);
                console.error("Error: " + response.status + " - " + response.message);
            }
        },
        error: function(xhr, status, error) {
            const response = JSON.parse(xhr.responseText);
            const errorMessage = response.message || 'Unknown error';
            showErrorMessage(errorMessage);
            console.error("Error adding the column:", error, "Response:", xhr.responseText);
        }
    });
}

export function saveRowData(baseApiUrl, rowData) {
    $.ajax({
        url: `${baseApiUrl}/api/rows`,
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(rowData),
        success: function(response) {
            if (response.status === 200) {
                showSuccessMessage(response.message);
                loadView('view-container', 'table', rowData.name);
            } else {
                showErrorMessage(response.message);
                console.error("Error: " + response.status + " - " + response.message);
            }
        },
        error: function(xhr, status, error) {
            const response = JSON.parse(xhr.responseText);
            const errorMessage = response.message || 'Unknown error';
            showErrorMessage(errorMessage);
            console.error("Error adding the row:", error, "Response:", xhr.responseText);
        }
    });
}

export function updateRowData(baseApiUrl, rowData) {
    $.ajax({
        url: `${baseApiUrl}/api/rows`,
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify(rowData),
        success: function(response) {
            if (response.status === 200) {
                showSuccessMessage(response.message);
                loadView('view-container', 'table', rowData.name);
            } else {
                showErrorMessage(response.message);
                console.error("Error: " + response.status + " - " + response.message);
            }
        },
        error: function(xhr, status, error) {
            const response = JSON.parse(xhr.responseText);
            const errorMessage = response.message || 'Unknown error';
            showErrorMessage(errorMessage);
            console.error("Error updating the row:", error, "Response:", xhr.responseText);
        }
    });
}

export function deleteRow(baseApiUrl, rowData) {
    $.ajax({
        url: `${baseApiUrl}/api/rows`,
        method: 'DELETE',
        contentType: 'application/json',
        data: JSON.stringify(rowData),
        success: function(response) {
            if (response.status === 200) {
                showSuccessMessage(response.message);
                loadView('view-container', 'table', rowData.name);
            } else {
                showErrorMessage(response.message);
                console.error("Error: " + response.status + " - " + response.message);
            }
        },
        error: function(xhr, status, error) {
            const response = JSON.parse(xhr.responseText);
            const errorMessage = response.message || 'Unknown error';
            showErrorMessage(errorMessage);
            console.error("Error deleting the column:", error, "Response:", xhr.responseText);
        }
    });
}
  