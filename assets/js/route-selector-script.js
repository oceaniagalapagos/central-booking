const prefix = {
    time: 'selector_route_time_',
    origin: 'selector_route_origin_',
    destiny: 'selector_route_destiny_',
    transport: 'selector_route_transport_',
};

const selectOrigins = document.querySelectorAll(`select[target^="${prefix.origin}"]`);

selectOrigins.forEach(selector => {
    const id = selector.getAttribute('target').replace(prefix.origin, '');
    const selects = {
        time: document.querySelector(`select[target="${prefix.time + id}"]`),
        destiny: document.querySelector(`select[target="${prefix.destiny + id}"]`),
        transport: document.querySelector(`select[target="${prefix.transport + id}"]`),
    };

    function filterOptions(select, conditionClass) {
        for (const option of select) {
            option.style.display = option.classList.contains(conditionClass) ? '' : 'none';
        }
        select.selectedIndex = 0;
        select.dispatchEvent(new Event('change'));
    }

    selector.addEventListener('change', () =>
        filterOptions(selects.destiny, `show_if_origin_${selector.value}`)
    );

    selects.destiny.addEventListener('change', () =>
        filterOptions(selects.time, `show_if_origin_${selector.value}_destiny_${selects.destiny.value}`)
    );

    selects.time.addEventListener('change', () =>
        filterOptions(selects.transport, `show_if_origin_${selector.value}_destiny_${selects.destiny.value}_time_${selects.time.value}`)
    );

    selector.dispatchEvent(new Event('change'));
});
