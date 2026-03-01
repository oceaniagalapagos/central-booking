/**
 * 
 * @param {Array} options Arreglo con las opciones.
 * @param {Function} displayOption Mapper que obtiene como se mostrará la opción.
 * @param {Function} doOption Funció que espeficicará que se hará con la opción.
 
 * @returns {HTMLElement} Un elemento con las opciones.
 */
export function createOptionPane(options, displayOption, doOption) {
    let container = create_option_container();
    let i = 0;

    for (const optionValue of options) {
        let option = create_item_option(optionValue, displayOption);
        if (i === 0) {
            option.classList.add('option-selected');
            doOption(optionValue);
        }
        container.appendChild(option);
        option.addEventListener('click', () => {
            container.childNodes.forEach(child => {
                child.classList.remove('option-selected');
            });
            option.classList.add('option-selected');
            doOption(optionValue);
        });
        i++;
    }
    return container;
}

/**
 * 
 * @returns {HTMLElement} Contenedor de opciones
 */
function create_option_container() {
    let container = document.createElement('div');
    container.classList.add('option-container');
    return container;
}

function create_item_option(item, display_option) {
    let option = document.createElement('div');
    option.classList.add('option', 'mb-1');
    const displayResult = display_option(item);
    if (displayResult instanceof Node) {
        option.appendChild(displayResult);
    } else {
        option.textContent = String(displayResult);
    }
    return option;
}