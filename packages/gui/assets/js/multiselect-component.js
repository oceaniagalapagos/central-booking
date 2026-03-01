document.addEventListener('DOMContentLoaded', function () {
    const selects = document.querySelectorAll('.git-multiselect');

    // Inicializar cada multiselect
    selects.forEach(select => {
        const container = getContainerBySelect(select);
        if (!container) return;

        initializeExistingOptions(select, container);
        setupSelectChangeHandler(select, container);
    });

    // Event delegation global para elementos dinámicos
    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('git-remove-option')) {
            event.preventDefault();
            event.stopPropagation();

            const selectedItem = event.target.closest('.git-option-item-selected');
            const container = selectedItem.closest('.git-multiselect-container');
            const selectId = container.id.replace('-container', '');
            const select = document.getElementById(selectId);

            removeOptionFromSelect(select, selectedItem);
        }
    });
});

/**
 * Inicializa opciones ya existentes en el contenedor
 */
function initializeExistingOptions(select, container) {
    const existingItems = getOptionsInContainer(container);
    existingItems.forEach(item => {
        const value = item.dataset.value;
        const optionElement = select.querySelector(`option[value="${value}"]`);
        if (optionElement) {
            optionElement.style.display = 'none';
        }
    });
}

/**
 * Configura el handler para cambios en el select
 */
function setupSelectChangeHandler(select, container) {
    select.addEventListener('change', function () {
        const value = this.value;
        if (!value) return;

        const success = addOptionToContainer(select, value);
        if (success) {
            this.selectedIndex = 0; // Resetear select
        }
    });
}

/**
 * Añade una opción específica al contenedor y la oculta del select
 * @param {HTMLSelectElement} select - El elemento select
 * @param {string|number} value - El valor de la opción a añadir
 * @returns {boolean} - true si se añadió exitosamente
 */
function addOptionToContainer(select, value) {
    // Validaciones
    if (!select || !value) {
        console.warn('Select element and value are required');
        return false;
    }

    const container = getContainerBySelect(select);
    if (!container) return false;

    const optionElement = select.querySelector(`option[value="${value}"]`);
    if (!optionElement) {
        console.warn(`Option with value "${value}" not found`);
        return false;
    }

    const selected = getSelectedValues(select);
    if (selected.includes(String(value))) {
        console.warn(`Option "${value}" already selected`);
        return false;
    }

    // Procesar adición
    const text = optionElement.textContent.trim();
    selected.push(String(value));

    // updateSelectedValues(select, selected);

    const selectedItem = createSelectedItemElement(select, value, text);
    container.appendChild(selectedItem);

    optionElement.style.display = 'none';

    return true;
}

/**
 * Remueve una opción del contenedor por valor
 * @param {HTMLSelectElement} select - El elemento select
 * @param {string|number} value - El valor a remover
 * @returns {boolean} - true si se removió exitosamente
 */
function removeOptionFromContainer(select, value) {
    const container = getContainerBySelect(select);
    if (!container) return false;

    const selectedItem = container.querySelector(`[data-value="${value}"]`);
    if (!selectedItem) return false;

    const selected = getSelectedValues(select);
    const updatedSelected = selected.filter(item => item !== String(value));

    updateSelectedValues(select, updatedSelected);
    selectedItem.remove();

    const optionElement = select.querySelector(`option[value="${value}"]`);
    if (optionElement) {
        optionElement.style.display = '';
    }

    return true;
}

/**
 * Remueve una opción usando el elemento DOM
 * @param {HTMLSelectElement} select - El elemento select
 * @param {HTMLElement} itemElement - El elemento DOM a remover
 */
function removeOptionFromSelect(select, itemElement) {
    const value = itemElement.dataset.value;
    removeOptionFromContainer(select, value);
}

/**
 * Crea el elemento DOM para una opción seleccionada
 * @param {HTMLSelectElement} select - El elemento select
 * @param {string|number} value - El valor de la opción
 * @param {string} text - El texto a mostrar
 * @returns {HTMLDivElement} - El elemento creado
 */
function createSelectedItemElement(select, value, text) {
    const name = (select.dataset.name || select.name || 'options') + '[]';

    const selectedItem = document.createElement('div');
    selectedItem.className = 'git-option-item-selected';
    selectedItem.dataset.value = String(value);

    const textSpan = document.createElement('span');
    textSpan.textContent = text;
    textSpan.className = 'option-text';

    const removeBtn = document.createElement('i');
    removeBtn.className = 'bi bi-x git-remove-option';
    removeBtn.style.cursor = 'pointer';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = String(value);

    selectedItem.appendChild(textSpan);
    selectedItem.appendChild(removeBtn);
    selectedItem.appendChild(input);

    return selectedItem;
}

/**
 * Obtiene los valores seleccionados del dataset
 * @param {HTMLSelectElement} select - El elemento select
 * @returns {Array} - Array de valores seleccionados
 */
function getSelectedValues(select) {
    try {
        const data = select.dataset.selected || '[]';
        return JSON.parse(data);
    } catch (e) {
        console.warn('Error parsing selected values:', e);
        select.dataset.selected = '[]';
        return [];
    }
}

/**
 * Actualiza los valores seleccionados en el dataset
 * @param {HTMLSelectElement} select - El elemento select
 * @param {Array} values - Array de valores
 */
function updateSelectedValues(select, values) {
    const serialized = JSON.stringify(values);
    select.dataset.selected = serialized;

    // También actualizar el atributo para PHP
    select.setAttribute('data-selected', serialized);
}

/**
 * Obtiene el contenedor asociado a un select
 * @param {HTMLSelectElement} select - El elemento select
 * @returns {HTMLElement|null} - El contenedor o null
 */
function getContainerBySelect(select) {
    if (!select?.id) {
        console.warn('Select element must have an ID');
        return null;
    }

    const containerId = select.id + '-container';
    const container = document.getElementById(containerId);

    if (!container) {
        console.warn(`Container with ID "${containerId}" not found`);
    }

    return container;
}

/**
 * Obtiene todas las opciones seleccionadas en un contenedor
 * @param {HTMLElement} container - El contenedor
 * @returns {NodeList} - Lista de elementos seleccionados
 */
function getOptionsInContainer(container) {
    return container.querySelectorAll('.git-option-item-selected');
}

// document.querySelectorAll('.git-multiselect').forEach(select => {
//     let form = null;
//     if (select.closest('form')) {
//         form = select.closest('form');
//     }
//     if (form) {
//         form.addEventListener('submit', function () {
//             const selectedValues = getSelectedValues(select);
//             for (const value of selectedValues) {
//                 const hiddenInput = document.createElement('input');
//                 hiddenInput.type = 'hidden';
//                 hiddenInput.name = select.name + '[]';
//                 hiddenInput.value = value;
//                 form.appendChild(hiddenInput);
//             }
//             select.name = ''; // Evitar envío del select original
//         });
//     }
// });

/**
 * API pública para uso externo
 */
window.MultiselectAPI = {
    addOption: addOptionToContainer,
    removeOption: removeOptionFromContainer,
    getSelected: getSelectedValues,
    getContainer: getContainerBySelect
};
