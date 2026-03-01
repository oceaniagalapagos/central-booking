/**
 * 
 * @param {HTMLInputElement} input 
 * @param {string} link 
 */
export function setInteractivePagination(input, link) {
    input.addEventListener('keypress', function (e) {
        e.preventDefault();
        if (e.key === 'Enter') {
            const page = parseInt(input.value, 10);
            if (!isNaN(page) && page > 0) {
                location.replace = link.replace('[page_number_placeholder]', page);
            } else {
                input.value = page;
            }
        }
    });
}
