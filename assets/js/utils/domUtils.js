export function handleFormSubmission(formSelector, apiFunction, getDataCallback) {
    $(document).on('submit', formSelector, (e) => {
        e.preventDefault();
        const { baseApiUrl, data } = getDataCallback();
        if (!data || !baseApiUrl) {
            console.error("Fehlende baseApiUrl oder data", baseApiUrl, data);
            return;
        }
        apiFunction(baseApiUrl, data);
    });
}

export function appendTemplateToElement(elementSelector, template) {
    $(elementSelector).append(template);
}

export function handleOutsideClick(event, options) {
    const { clickListenerEnabled, excludeSelector, getRenameData, onConfirm, onCancel } = options;

    if (clickListenerEnabled() && !$(event.target).closest(excludeSelector).length) {
        const renameData = getRenameData();

        if (renameData.oldName !== renameData.newName) {
            onConfirm(renameData);
        } else {
            onCancel();
        }

        options.disableClickListener();
    }
}

export function toggleAndFocus(inputWrapper, display, inputFocus) {
    toggleElementPairVisibility(inputWrapper, display);
    focusInputFieldById(inputFocus);
}

export function processInputValue(input) {
    if (!input.value && input.type !== 'checkbox') return null;
    return input.type === 'checkbox' ? $(input).prop('checked') ? 1 : 0 : input.value;
}

export function toggleElementPairVisibility(showElementId, hideElementId) {
    $(`#${showElementId}`).removeClass('hidden'); // Show the specified element by removing the 'hidden' class
    $(`#${hideElementId}`).addClass('hidden'); // Hide the other element by adding the 'hidden' class
}

export function focusInputFieldById(inputId) {
    $(`#${inputId}`).focus(); // Focus on the input field with the provided ID
    const input = $(`#${inputId}`)[0];
    input.setSelectionRange(input.value.length, input.value.length); // Set the cursor to the end of the text in the input field
}