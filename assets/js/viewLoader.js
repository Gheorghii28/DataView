export function loadView(containerId, view, tableName = null) {
    let url = "controllers/load_view.php?view=" + view;
    const xmlhttp = new XMLHttpRequest();
    if (tableName) {
        url += "&table_name=" + encodeURIComponent(tableName);
    }
 
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById(containerId).innerHTML = xmlhttp.responseText;
        }
    };
 
    xmlhttp.open("GET", url, true);
    xmlhttp.send();
}