import { getConfigData } from "./utils/configUtils.js";

export function resetForm(modalSelector) {
    $(modalSelector).click();
    $(modalSelector + " .removable").remove();
    $(modalSelector + " .clearable").val('');
}

export function getTableData(tableName) {
    const { baseApiUrl, userId } = getConfigData();
    const columns = getColumnsData();

    return {
        baseApiUrl: baseApiUrl,
        data: {
            name: tableName,
            columns: columns,
            userId: userId,
        },
    };
}

export function getColumnsData() {
    const columns = {};

    $("input[name='column_name[]']").each(function (index) {
        const columnName = $(this).val();
        const columnType = $("select[name='column_type[]']").eq(index).val();
        if (columnName && columnType) {
            columns[columnName] = columnType;
        }
    });

    return columns;
}

export function getTableDataForDeletion() {
    const { baseApiUrl, userId } = getConfigData();
    const tableName = $('#deleteTableForm').data('table-name');

    return {
        baseApiUrl: baseApiUrl,
        data: {
            tableName: tableName,
            userId: userId,
        },
    };
}

export function updateElementTextAndValue(displayElementId, inputElementId, oldValueElementId, prefix = '') {
    const newValue = $(`#${inputElementId}`).val(); // Get the value from the input field
    $(`#${displayElementId}`).text(`${prefix}${newValue}`); // Update the text of the display element
    if (oldValueElementId) {
        $(`#${oldValueElementId}`).val(newValue); // Update the value of the old element
    }
}

export function updateDeleteConfirmation(formSelector, deleteMessage, newTableName, dataAttribute) {
    $(formSelector).attr(`data-${dataAttribute}`, newTableName); // Update the data attribute of the form
    $('#deleteMessage').text(deleteMessage); // Update the delete confirmation message
}

export function getRowData(tableName, data) {
    const { baseApiUrl, userId } = getConfigData();
    data.user_id = userId;
    return {
        baseApiUrl: baseApiUrl,
        rowData: {
            name: tableName,
            userId: userId,
            data: data,
        }
    };
}

export function getRowId(button) {
    const buttonId = $(button).attr('id'); // Get the ID of the clicked button
    return buttonId.split('-')[1]; // Extract and return the row ID
}

export function getTableName() {
    return $('#tableNameDisplay').text().replace('Table: ', '').trim();
}

export function resetInputs($row) {
    $row.find('input').each(function () {
        if ($(this).attr('type') === 'checkbox') {
            const originalChecked = $(this).attr('data-original') === 'true';
            $(this).prop('checked', originalChecked);
        } else {
            const originalValue = $(this).attr('data-original') || '';
            $(this).val(originalValue);
        }
    });
}