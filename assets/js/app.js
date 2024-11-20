import { loadView } from './viewLoader.js';
import { loadScript } from './scriptLoader.js';
import { getUserTables } from './tableApi.js';

window.loadView = loadView; // Make loadView globally available

const scripts = [
    'assets/js/create_table_form.js', // Include the external JS file for form submission and dynamic column management
];
const baseApiUrl = $('#config').data('api-url');
const userId = $('#config').data('user-id');

loadView('view-container', 'dashboard'); // Call the loadView function to load the 'dashboard' view into the container with the ID 'view-container'

scripts.forEach(function(script) {
    loadScript(script);
});

getUserTables(baseApiUrl, userId);