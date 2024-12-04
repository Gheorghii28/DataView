import { getConfigData } from "./utils/configUtils.js";

// Reusable function to initialize sortable elements
function initSortable(containerId, dataAttribute, saveOrderFunction) {
    const { baseApiUrl, userId } = getConfigData();
    $(containerId).sortable({
        update: function(event, ui) {
            const newOrder = $(this) // Determine the new order
                .children()
                .map(function() {
                    return $(this).data(dataAttribute); // Extract the data attribute (table-id, column-name, or row-id)
                })
                .get();

            saveOrderFunction(baseApiUrl, userId, newOrder); // AJAX call to save the order
        }
    });
}

export function initTableSortable(saveTableOrder) {
    initSortable("#table-list-container", 'table-id', saveTableOrder); // Initialize table sortable
}

export function initColumnSortable(saveColumnOrder) {
    initSortable("#column-list-container", 'column-name', saveColumnOrder); // Initialize column sortable
}

export function initRowSortable(saveRowOrder) {
    initSortable("#row-list-container", 'row-id', saveRowOrder); // Initialize row sortable
}
