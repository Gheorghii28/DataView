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
        tableData: {
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
        tableData: {
            tableName: tableName,
            userId: userId,
        },
    };
}

export function getConfigData() {
    const baseApiUrl = $('#config').data('api-url');
    const userId = $('#config').data('user-id');
    return { baseApiUrl, userId };
}

export function toggleElementPairVisibility(showElementId, hideElementId) {
    $(`#${showElementId}`).removeClass('hidden'); // Show the specified element by removing the 'hidden' class
    $(`#${hideElementId}`).addClass('hidden'); // Hide the other element by adding the 'hidden' class
}

export function updateElementTextAndValue(displayElementId, inputElementId, oldValueElementId, prefix = '') {
    const newValue = $(`#${inputElementId}`).val(); // Get the value from the input field
    $(`#${displayElementId}`).text(`${prefix}${newValue}`); // Update the text of the display element
    if (oldValueElementId) {
        $(`#${oldValueElementId}`).val(newValue); // Update the value of the old element
    }
}

export function focusInputFieldById(inputId) {
    $(`#${inputId}`).focus(); // Focus on the input field with the provided ID
    const input = $(`#${inputId}`)[0];
    input.setSelectionRange(input.value.length, input.value.length); // Set the cursor to the end of the text in the input field
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
