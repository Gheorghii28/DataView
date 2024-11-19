export function showSuccessMessage(message) {
    $('#successButton').click();
    $("#success-message").text(message);
}

export function showErrorMessage(errorMessage) {
    $('#errorButton').click();
    $("#error-message").text(errorMessage);
}
