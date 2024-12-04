import { initColumnSortable, initRowSortable } from "./sortable.js";
import { saveColumnOrder, saveRowOrder } from "./tableApi.js";

export function loadView(containerId, view, tableName = null) {
    let url = "controllers/load_view.php?view=" + view;
    const xmlhttp = new XMLHttpRequest();
    if (tableName) {
        url += "&table_name=" + encodeURIComponent(tableName);
    }
 
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById(containerId).innerHTML = xmlhttp.responseText; // Inject the received HTML into the container
            window.initFlowbite(); // Reinitialize Flowbite components after the DOM is updated
            if(tableName) {
                initColumnSortable(saveColumnOrder);
                initRowSortable(saveRowOrder);
            }
        }
    };
 
    xmlhttp.open("GET", url, true);
    xmlhttp.send();
}