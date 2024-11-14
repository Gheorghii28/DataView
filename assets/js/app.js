function loadView(containerId, view) {
   const url = "controllers/load_view.php?view=" + view;
   const xmlhttp = new XMLHttpRequest();

   xmlhttp.onreadystatechange = function() {
       if (this.readyState == 4 && this.status == 200) {
           document.getElementById(containerId).innerHTML = xmlhttp.responseText;
       }
   };

   xmlhttp.open("GET", url, true);
   xmlhttp.send();
}

loadView('view-container', 'dashboard'); // Call the loadView function to load the 'dashboard' view into the container with the ID 'view-container'