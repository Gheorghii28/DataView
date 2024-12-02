import { selectors } from "../selectors.js";
import { enableRenameColumnClickListener, getSelectedColumnIds, updateSelectedColumnIds } from "../state/clickListenerState.js";
import { interactiveColumnHighlightClass, interactiveDeleteHighlightClass } from "../styleConstants.js";
import { tableColumnTemplate } from "../templates.js";
import { appendTemplateToElement, toggleAndFocus } from "../utils/domUtils.js";
import { attachClickHandler } from "../utils/eventUtils.js";

export function addColumnHandler(e) {
    e.preventDefault();
    appendTemplateToElement('#columns', tableColumnTemplate);
}

export function addNewColumnHandler(e) {
    e.preventDefault();
    appendTemplateToElement('#addColumns', tableColumnTemplate);
}

export function removeColumnHandler(e) {
    e.preventDefault();
    $(e.target).closest('.column').remove();
}

export function renameColumnHandler() {
    $('.column-header').addClass(interactiveColumnHighlightClass);
    attachClickHandler(selectors.columnHeader, handleColumnRename);
}

function handleColumnRename() {
    updateSelectedColumnIds({
        columnNameInputWrapper: $(this).data('input-wrapper-id'),
        columnNameDisplay: $(this).data('column-display-id'),
        newColumnName: $(this).data('new-name-input-id'),
        oldColumnName: $(this).data('old-name-input-id'),
    });
    const selectedColumnIds = getSelectedColumnIds();
    toggleAndFocus(selectedColumnIds.columnNameInputWrapper, selectedColumnIds.columnNameDisplay, selectedColumnIds.newColumnName);
    enableRenameColumnClickListener();

    $('.column-header').removeClass(interactiveColumnHighlightClass).off('click');
}

export function deleteColumnHandler() {
    $('#columnTriggerId').addClass('hidden');
    $('.column-header').addClass(interactiveDeleteHighlightClass).on('click', handleColumnDelete);
}

function handleColumnDelete() {
    const columnName = $(this).data('column-name');
    $('#deleteColumnConfirmationBtn').attr('data-column-name', columnName).click();

    $('.column-header').removeClass(interactiveDeleteHighlightClass).off('click');
}
