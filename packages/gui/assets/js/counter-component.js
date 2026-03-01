const counters = document.getElementsByClassName('git-counter');
const counterValues = {};

Array.from(counters).forEach(counter => {
    counterValues[counter.id] = {
        min: counter.dataset.minValue ? parseInt(counter.dataset.minValue) : undefined,
        max: counter.dataset.maxValue ? parseInt(counter.dataset.maxValue) : undefined,
        value: parseInt(counter.querySelector('.counter-value').textContent, 10),
    };
    const minusButton = counter.querySelector('.counter-decrement');
    const plusButton = counter.querySelector('.counter-increment');
    const value = counter.querySelector('.counter-value');

    minusButton.addEventListener('click', () => {
        const minValue = counterValues[counter.id].min;
        if (minValue === undefined || counterValues[counter.id].value > minValue) {
            counterValues[counter.id].value--;
            value.textContent = counterValues[counter.id].value;
        }
    });

    plusButton.addEventListener('click', () => {
        const maxValue = counterValues[counter.id].max;
        if (maxValue === undefined || counterValues[counter.id].value < maxValue) {
            counterValues[counter.id].value++;
            value.textContent = counterValues[counter.id].value;
        }
    });
});

/**
 * @param {number} max
 * @param {HTMLDivElement[]} counters
 */
function setMaximunCombine(max, counters) {
    counters.forEach(counter => {
        const minusButton = counter.querySelector('.counter-decrement');
        const plusButton = counter.querySelector('.counter-increment');
        const value = counter.querySelector('.counter-value');
        // Clona los botones para eliminar listeners previos
        minusButton.replaceWith(minusButton.cloneNode(true));
        plusButton.replaceWith(plusButton.cloneNode(true));
    });

    // Reasigna eventos combinados
    counters.forEach(counter => {
        const minusButton = counter.querySelector('.counter-decrement');
        const plusButton = counter.querySelector('.counter-increment');
        const value = counter.querySelector('.counter-value');

        minusButton.addEventListener('click', () => {
            if (counterValues[counter.id].value > 0) {
                counterValues[counter.id].value--;
                value.textContent = counterValues[counter.id].value;
            }
        });

        plusButton.addEventListener('click', () => {
            const total = Array.from(counters).reduce((sum, c) => sum + counterValues[c.id].value, 0);
            if (counterValues[counter.id].value < (counterValues[counter.id].max ?? max) && total < max) {
                counterValues[counter.id].value++;
                value.textContent = counterValues[counter.id].value;
            }
        });
    });
}

/**
 * @param {HTMLDivElement} counter 
 * @returns value
 */
function getValue(counter) {
    return counterValues[counter.id] ? counterValues[counter.id].value : 0;
}

window.GIT_Counter = {
    getValue: getValue,
    setMaximunCombine: setMaximunCombine
};