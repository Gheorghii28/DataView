import { loadView } from './viewLoader.js';
import { loadScript } from './scriptLoader.js';
import { getUserTables } from './tableApi.js';

window.loadView = loadView; // Make loadView globally available

const scripts = [
    'assets/js/uiEvents.js', // Contains event handlers and functions for user interface interactions
];
const baseApiUrl = $('#config').data('api-url');
const userId = $('#config').data('user-id');

loadView('view-container', 'dashboard'); // Call the loadView function to load the 'dashboard' view into the container with the ID 'view-container'

scripts.forEach(function(script) {
    loadScript(script);
});

getUserTables(baseApiUrl, userId);