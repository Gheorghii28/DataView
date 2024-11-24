export function showSuccessMessage(message, loadViewAllowed = false) {
    $('#successButton').click();
    $("#success-message").text(message);
    window.loadViewAllowed = loadViewAllowed; // Saves the value of loadViewAllowed globally in the window object, so it can be checked later (for example, when clicking on the modal)
}

export function showErrorMessage(errorMessage) {
    $('#errorButton').click();
    $("#error-message").text(errorMessage);
}
