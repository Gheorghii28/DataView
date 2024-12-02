// Module to manage click listener states and selected column IDs

let renameTableClickListenerEnabled = false;
let renameColumnClickListenerEnabled = false;
let selectedColumnIds = null;

// Rename Table Click Listener State
export function enableRenameTableClickListener() {
    renameTableClickListenerEnabled = true;
}

export function disableRenameTableClickListener() {
    renameTableClickListenerEnabled = false;
}

export function isRenameTableClickListenerEnabled() {
    return renameTableClickListenerEnabled;
}

// Rename Column Click Listener State
export function enableRenameColumnClickListener() {
    renameColumnClickListenerEnabled = true;
}

export function disableRenameColumnClickListener() {
    renameColumnClickListenerEnabled = false;
}

export function isRenameColumnClickListenerEnabled() {
    return renameColumnClickListenerEnabled;
}

// Selected Column IDs State
export function updateSelectedColumnIds(newIds) {
    selectedColumnIds = newIds;
}

export function getSelectedColumnIds() {
    return selectedColumnIds;
}
