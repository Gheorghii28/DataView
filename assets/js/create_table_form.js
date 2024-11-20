import { tableColumnTemplate } from './templates.js';
import { getTableData } from './formHelpers.js';
import { createTable } from './tableApi.js';

$(document).ready(function () {
    $("#createTableForm").on("submit", function (e) {
        e.preventDefault();
        
        const { baseApiUrl, tableData } = getTableData();

        createTable(baseApiUrl, tableData);      
    });

    $('#addColumnBtn').on("click", function (e) { // dynamically add more columns
        e.preventDefault();
        $("#columns").append(tableColumnTemplate);
    })

    $(document).on("click", ".remove-column-btn", function (e) { // dynamically remove column
        e.preventDefault();
        const columnDiv = $(this).closest('.column');
        columnDiv.remove();
    });   
});
