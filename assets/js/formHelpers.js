export function resetForm(modalSelector) {
    $(modalSelector).click();
    $(modalSelector + " .removable").remove();
    $(modalSelector + " .clearable").val('');
}

export function getTableData() {
    const config = getConfigData();
    const baseApiUrl = config.baseApiUrl;
    const userId = config.userId;
    const tableName = $("input[name='table_name']").val();
    const columns = {};

    $("input[name='column_name[]']").each(function (index) {
        const columnName = $(this).val();
        const columnType = $("select[name='column_type[]']").eq(index).val();
        if (columnName && columnType) {
            columns[columnName] = columnType;
        }
    });

    return {
        baseApiUrl: baseApiUrl,
        tableData: {
            name: tableName,
            columns: columns,
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
