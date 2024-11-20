export function resetForm(modalSelector) {
    $(modalSelector).click();
    $(modalSelector + " .removable").remove();
    $(modalSelector + " .clearable").val('');
}

export function getTableData() {
    const baseApiUrl = $('#config').data('api-url');
    const userId = $('#config').data('user-id');
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
