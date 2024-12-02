export function getConfigData() {
    const baseApiUrl = $('#config').data('api-url');
    const userId = $('#config').data('user-id');
    return { baseApiUrl, userId };
}