export function openPdfFromBase64(pdfBase64) {
    try {
        // Convert the Base64-encoded PDF into a Blob object
        const byteCharacters = atob(pdfBase64);
        const byteNumbers = new Array(byteCharacters.length);
        for (let i = 0; i < byteCharacters.length; i++) {
            byteNumbers[i] = byteCharacters.charCodeAt(i);
        }
        const byteArray = new Uint8Array(byteNumbers);
        const blob = new Blob([byteArray], { type: 'application/pdf' });
        
        // Create a URL for the Blob and open it in a new tab
        const blobUrl = URL.createObjectURL(blob);
        window.open(blobUrl, '_blank');
    } catch (error) {
        console.error("Error while creating the PDF:", error);
    }
}

export function sendPdfDownloadRequest(url, action, tableData, tableName) {
    // Create an invisible form
    const form = $('<form>', {
        action: url,
        method: 'POST',
        target: '_blank'
    }).append($('<input>', {
        type: 'hidden',
        name: 'action',
        value: action
    })).append($('<input>', {
        type: 'hidden',
        name: 'tableName',
        value: tableName
    })).append($('<input>', {
        type: 'hidden',
        name: 'tableData',
        value: JSON.stringify(tableData)
    }));

    // Append the form to the document and submit it
    $('body').append(form);
    form.trigger('submit');
    form.remove();
}