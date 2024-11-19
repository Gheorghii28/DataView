import { tableColumnTemplate } from './templates.js';
import { showSuccessMessage, showErrorMessage } from './notification.js';
import { resetForm, getTableData } from './formHelpers.js';

$(document).ready(function () {
    $("#createTableForm").on("submit", function (e) {
        e.preventDefault();
        
        const { baseApiUrl, tableData } = getTableData();

        $.ajax({
            url: `${baseApiUrl}/api/table`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(tableData),
            success: function (response) {
                if (response.status == 200) {
                    resetForm('#formCreateTableModal');
                    showSuccessMessage(response.message);
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
