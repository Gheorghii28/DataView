import { enableRenameTableClickListener } from "../state/clickListenerState.js";
import { toggleAndFocus, toggleElementPairVisibility } from "../utils/domUtils.js";

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