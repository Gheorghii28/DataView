import { showSuccessMessage, showErrorMessage } from './notification.js';
import { getTableName, resetForm, updateDeleteConfirmation, updateElementTextAndValue } from './formHelpers.js';
import { tableListItemTemplate } from './templates.js';
import { loadView } from './viewLoader.js';
import { toggleElementPairVisibility } from './utils/domUtils.js';
import { initTableSortable } from './sortable.js';
import { getConfigData } from './utils/configUtils.js';
import { openPdfFromBase64 } from './utils/pdfUtils.js';

// Reusable AJAX function
function ajaxRequest({ baseApiUrl, endpoint, method, data, loadViewallowed = false, successCallback }) {
    $.ajax({
        url: `${baseApiUrl}${endpoint}`,
        method: method,
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function (response) {
            if (response.status === 200) {
                showSuccessMessage(response.message, loadViewallowed);
                if (successCallback) successCallback(response);
            } else {
                showErrorMessage(response.message);
                console.error(`Error: ${response.status} - ${response.message}`);
            }
        },
        error: function (xhr, status, error) {
            const response = JSON.parse(xhr.responseText || '{}');
            const errorMessage = response.message || 'Unknown error';
            showErrorMessage(errorMessage);
            console.error(`Error: ${error}`, "Response:", xhr.responseText);
        }
    });
}

export function createTable(baseApiUrl, tableData) {
    ajaxRequest({
        baseApiUrl,
        endpoint: '/api/table',
        method: 'POST',
        data: tableData,
        successCallback: () => {
            resetForm('#formCreateTableModal');
            getUserTables(baseApiUrl, tableData.userId);
        }
    });
}

export function deleteTable(baseApiUrl, tableData) {
    ajaxRequest({
        baseApiUrl,
        endpoint: '/api/table',
        method: 'DELETE',
        data: tableData,
        loadViewallowed: true,
        successCallback: () => {
            getUserTables(baseApiUrl, tableData.userId);
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

                response.data.forEach(function(table, index) {
                    const tableItem = tableListItemTemplate(table.name, table.id);
                    tableListContainer.append(tableItem);
                });

                initTableSortable(saveTableOrder);
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
    ajaxRequest({
        baseApiUrl,
        endpoint: '/api/renameTable',
        method: 'PUT',
        data: renameData,
        successCallback: (response) => {
            getUserTables(baseApiUrl, renameData.userId);
            toggleElementPairVisibility('tableNameDisplay', 'tableNameInputWrapper');
            updateElementTextAndValue('tableNameDisplay', 'newTableName', 'oldTableName', 'Table: ');
            updateDeleteConfirmation(
                '#deleteTableForm',
                `Are you sure you want to delete the table "${response.data.newName}"?`,
                response.data.newName,
                'table-name'
            );
        }
    });
}

export function renameColumn(baseApiUrl, renameData, ids) {
    ajaxRequest({
        baseApiUrl,
        endpoint: '/api/column',
        method: 'PUT',
        data: renameData,
        successCallback: () => {
            toggleElementPairVisibility(ids.columnNameDisplay, ids.columnNameInputWrapper);
            updateElementTextAndValue(ids.columnNameDisplay, ids.newColumnName, ids.oldColumnName);
            loadView('view-container', 'table', renameData.tableName);
        }
    });
}

export function addColumn(baseApiUrl, tableData) {
    ajaxRequest({
        baseApiUrl,
        endpoint: '/api/column',
        method: 'POST',
        data: tableData,
        successCallback: () => {
            resetForm('#formAddColumnModal');
            loadView('view-container', 'table', tableData.tableName);
        }
    });
}

export function deleteColumn(baseApiUrl, tableData) {
    ajaxRequest({
        baseApiUrl,
        endpoint: '/api/column',
        method: 'DELETE',
        data: tableData,
        successCallback: () => loadView('view-container', 'table', tableData.tableName)
    });
}

export function saveRowData(baseApiUrl, rowData) {
    ajaxRequest({
        baseApiUrl,
        endpoint: '/api/rows',
        method: 'POST',
        data: rowData,
        successCallback: () => loadView('view-container', 'table', rowData.tableName)
    });
}

export function updateRowData(baseApiUrl, rowData) {
    ajaxRequest({
        baseApiUrl,
        endpoint: '/api/rows',
        method: 'PUT',
        data: rowData,
        successCallback: () => loadView('view-container', 'table', rowData.tableName)
    });
}

export function deleteRow(baseApiUrl, rowData) {
    ajaxRequest({
        baseApiUrl,
        endpoint: '/api/rows',
        method: 'DELETE',
        data: rowData,
        successCallback: () => loadView('view-container', 'table', rowData.tableName)
    });
}

function saveTableOrder(baseApiUrl, userId, newOrder) {
    $.ajax({
        url: `${baseApiUrl}/api/user/tables`,
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            userId: userId,
            order: newOrder,
        }),
        success: function(response) {
            getUserTables(baseApiUrl, userId);
        },
        error: function(xhr, status, error) {
            console.error("Error updating the order:", error);
        }
    });
}

export function saveColumnOrder(baseApiUrl, userId, newOrder) {
    const tableName = getTableName();
    $.ajax({
        url: `${baseApiUrl}/api/reorder/columns`,
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            userId: userId,
            tableName: tableName,
            order: newOrder,
        }),
        success: function(response) {
            loadView('view-container', 'table', tableName);
        },
        error: function(xhr, status, error) {
            console.error("Error updating the order:", error);
        }
    });
}

export function saveRowOrder(baseApiUrl, userId, newOrder) {
    const tableName = getTableName();
    $.ajax({
        url: `${baseApiUrl}/api/reorder/rows`,
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            userId: userId,
            tableName: tableName,
            order: newOrder,
        }),
        success: function(response) {
            loadView('view-container', 'table', tableName);
        },
        error: function(xhr, status, error) {
            console.error("Error updating the order:", error);
        }
    });
}

export function getTable(callback) {
    const tableName = getTableName();
    const { baseApiUrl, userId } = getConfigData();
    const url = `${baseApiUrl}/api/table?userId=${encodeURIComponent(userId)}&tableName=${encodeURIComponent(tableName)}`;

    $.ajax({
        url: url,
        method: 'GET',
        contentType: 'application/json',

        success: function(response) {
            callback(response.data, tableName);
        },
        error: function(xhr, status, error) {
            console.error("Error getting the table:", error);
        }
    });
}

export function exportTableAsPDF(url, action, tableData, tableName) {
    $.ajax({
        url: url,
        method: 'POST',
        data: {
            action: action,
            tableName: tableName,
            tableData: JSON.stringify(tableData)
        },
        success: function(response) {
            openPdfFromBase64(response.pdf_base64);
        },
        error: function(xhr, status, error) {
            console.error("Error during export:", error);
        }
    });
}