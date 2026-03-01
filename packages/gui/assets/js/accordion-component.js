document
    .querySelectorAll('.accordion-button')
    .forEach(button => {
        button.addEventListener('click', () => {
            eventHandledToogleVisible(button);
        });
    });

/**
* Maneja el evento de toggle de visibilidad del acordeón
* @param {HTMLButtonElement} button - El botón del acordeón
*/
function eventHandledToogleVisible(button) {
    const target = button.dataset.target;
    if (!target) {
        console.warn('Button must have a data-target attribute');
        return;
    }

    const item = document.querySelector(target);
    if (!item) {
        console.warn(`Target element "${target}" not found`);
        return;
    }

    // Manejar acordeón con comportamiento de grupo (solo uno abierto)
    if (item.dataset.parent) {
        const parentSelector = item.dataset.parent;
        const parent = document.querySelector(parentSelector);

        if (parent) {
            // Cerrar todos los otros elementos del acordeón
            parent.querySelectorAll('.accordion-collapse').forEach(collapse => {
                if (collapse !== item && collapse.classList.contains('show')) {
                    collapse.classList.remove('show');

                    // Actualizar el estado del botón correspondiente
                    const relatedButton = parent.querySelector(`[data-target="#${collapse.id}"]`);
                    if (relatedButton) {
                        relatedButton.classList.add('collapsed');
                        relatedButton.setAttribute('aria-expanded', 'false');
                    }
                }
            });
        }
    }

    // Toggle del elemento actual
    const isCurrentlyOpen = item.classList.contains('show');
    item.classList.toggle('show');

    // Actualizar atributos del botón para accesibilidad
    button.classList.toggle('collapsed', isCurrentlyOpen);
    button.setAttribute('aria-expanded', (!isCurrentlyOpen).toString());

    // Dispatch evento personalizado para hooks externos
    const eventType = isCurrentlyOpen ? 'accordion:closed' : 'accordion:opened';
    item.dispatchEvent(new CustomEvent(eventType, {
        detail: {
            button: button,
            item: item,
            target: target
        },
        bubbles: true
    }));
}

/**
 * 
 * @param {HTMLDivElement} accordion 
 * @param {HTMLElement} headerElement 
 * @param {HTMLElement} contentElement 
 */
function addItemToAccordion(accordion, headerElement, contentElement) {
    if (!accordion.classList.contains('git-accordion')) return;
    const item = document.createElement('div');
    const header = document.createElement('div');
    const collapsable = document.createElement('div');
    const collapsableContent = document.createElement('div');
    const headerButton = document.createElement('button');

    const itemId = 'accordion-item-' + Math.round(Math.random() * 100000000);

    headerButton.appendChild(headerElement);
    headerButton.className = 'accordion-button';
    headerButton.dataset.target = '#' + itemId;
    collapsableContent.className = 'accordion-body';
    collapsableContent.appendChild(contentElement);
    headerButton.type = 'button';

    item.className = 'accordion-item';
    header.className = 'accordion-header';
    header.appendChild(headerButton);
    collapsable.className = 'accordion-collapse collapse';
    collapsable.id = itemId;
    collapsable.dataset.parent = '#' + accordion.id;

    item.appendChild(header);
    item.appendChild(collapsable);
    collapsable.appendChild(collapsableContent);
    accordion.appendChild(item);

    headerButton.addEventListener('click', () => {
        eventHandledToogleVisible(headerButton);
    });

    return itemId;
}

window.AccordionComponentAPI = {
    addItem: addItemToAccordion
};