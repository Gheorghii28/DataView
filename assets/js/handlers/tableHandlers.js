import { enableRenameTableClickListener } from "../state/clickListenerState.js";
import { exportTableAsPDF, getTable } from "../tableApi.js";
import { toggleAndFocus, toggleElementPairVisibility } from "../utils/domUtils.js";
import { sendPdfDownloadRequest } from "../utils/pdfUtils.js";

export function renameTableHandler(e) {
    e.preventDefault();
    toggleAndFocus('tableNameInputWrapper', 'tableNameDisplay', 'newTableName');
    enableRenameTableClickListener();
}

export function deleteTableHandler(e) {
    e.preventDefault();
    $('#deleteTableConfirmationBtn').click();
}

export function addColumnButtonHandler() {
    $('#columnTriggerId').addClass('hidden');
    $('#addColumnModalButton').click();
}

export function addRowButtonHandler() {
    toggleElementPairVisibility('loopRow-new', 'columnTriggerId');
    $('.column-input-field').first().focus();
}

export function successModalToggleHandler() {
    if (window.loadViewAllowed) {
        loadView('view-container', 'dashboard');
    }
}

export function userDropdownHandler() {
    $('#user-dropdown').addClass('hidden');
}

export function viewPdfHandler() {
    const url = '../../export/export_pdf.php';
    const action = $(this).data('action');
    
    getTable(function(tableData, tableName) {
        exportTableAsPDF(url, action, tableData, tableName);
    });
}

export function downloadPdfHandler() {
    const url = '../../export/export_pdf.php';
    const action = $(this).data('action');

    getTable(function(tableData, tableName) {
        sendPdfDownloadRequest(url, action, tableData, tableName);
    });
}