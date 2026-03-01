export function createCell(content) {
    const cell = document.createElement('td');
    if (typeof content === 'string' ||
        typeof content === 'number' ||
        typeof content === 'boolean') {
        cell.textContent = content;
    } else if (content instanceof Node) {
        cell.appendChild(content);
    }
    return cell;
}

export function createContentExcel(content) {
    const container = createCell(content);
    container.classList.add('bubble-container');
    return container;
}

export function createContentExcelDimiss(content, dimissFunction) {
    const container = createContentExcel(content);
    const dimiss = document.createElement('i');
    container.appendChild(dimiss);
    dimiss.classList.add('bi', 'bi-x', 'ms-2');
    dimiss.style.cursor = 'pointer';
    dimiss.addEventListener('click', () => {
        dimissFunction();
        container.remove();
    });
    return container;
}
