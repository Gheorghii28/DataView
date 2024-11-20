import { showSuccessMessage, showErrorMessage } from './notification.js';
import { resetForm } from './formHelpers.js';
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