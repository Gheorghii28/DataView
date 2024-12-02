import { getRowData, getRowId, getTableName } from "../formHelpers.js";
import { saveRowData, updateRowData } from "../tableApi.js";
import { processInputValue, toggleElementPairVisibility } from "../utils/domUtils.js";

export function addDataHandler() {
    const rowId = getRowId(this);
    const inputsData = collectRowInputs(rowId);
    if (!inputsData) return;

    const { baseApiUrl, rowData } = getRowData(getTableName(), inputsData);
    rowId === 'new' ? saveRowData(baseApiUrl, rowData) : updateRowData(baseApiUrl, { ...rowData, rowId });
}

function collectRowInputs(rowId) {
    const inputsData = {};
    let isValid = true;

    $(`#loopRow-${rowId}`).closest('tr').find('input').each(function () {
        const inputName = $(this).attr('name');
        const inputValue = processInputValue(this);

        if (inputValue === null) {
            $(this).focus();
            isValid = false;
            return false;
        }

        inputsData[inputName] = inputValue;
    });

    return isValid ? inputsData : null;
}

export function deleteRowHandler(e) {
    const rowId = getRowId(e.target);
    $('#deleteRowConfirmationBtn').click(); // Trigger the click event to show the delete confirmation modal
    $('#deleteRowForm').attr('data-row-id', rowId); // Store the row id
}

export function renameRowHandler(e) {
    const rowId = getRowId(e.target);
    const inputField = $(`#loopRow-${rowId} .column-input-field`).first();
    inputField.focus()[0].setSelectionRange(inputField.val().length, inputField.val().length);
    $(`#rowTriggerId${rowId}`).addClass('hidden');
    toggleElementPairVisibility(`loopRow-${rowId}`, `row-${rowId}`);
}