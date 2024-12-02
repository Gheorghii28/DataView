export function attachClickHandler(selector, handler) {
    $(document).on('click', selector, handler);
}
