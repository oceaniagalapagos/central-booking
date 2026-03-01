const form = document.getElementById('product_form');
const formOverlay = document.getElementById('overlay_loading');

function toggleOverlay(visible) {
    formOverlay.style.display = visible ? '' : 'none';
}

function handleMessageModal(message) {
    document.getElementById('message_form_modal').innerHTML = message;
    document.getElementById('button_launch_modal_form').click();
}

form.addEventListener('submit', (e) => {
    const createInputHidden = (name, value) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        return input;
    };

    const roundTrip = () => {
        const inputs = document.querySelectorAll('input[name="type_way"]');
        return Array.from(inputs).some((input) => input.value === 'double_way' && input.checked);
    };

    const flexible = () => {
        return form.querySelector('input[name="flexible"]').checked;
    };

    const body = {
        nonce: form.querySelector('input[name="_gitnonce"]').value,
        product: form.querySelector('input[name="product"]').value,
        pax: {
            kid: window.CentralTickets.formProduct.getPax().kid,
            rpm: window.CentralTickets.formProduct.getPax().rpm,
            extra: window.CentralTickets.formProduct.getPax().extra,
            standard: window.CentralTickets.formProduct.getPax().standard
        },
        flexible: flexible(),
        round_trip: roundTrip(),
        goes: {
            date_trip: form.querySelector('input[name="date_trip_goes"]').value,
            id_route: window.CentralTickets.formProduct.getRoutes().goes.id,
            id_transport: window.CentralTickets.formProduct.getTransports().goes.id
        },
        returns: roundTrip() ? {
            date_trip: form.querySelector('input[name="date_trip_returns"]').value,
            id_route: window.CentralTickets.formProduct.getRoutes().returns.id,
            id_transport: window.CentralTickets.formProduct.getTransports().returns.id
        } : { date_trip: '', id_route: 0, id_transport: 0 },
        passengers: Array.from(document.getElementsByClassName('form_passenger')).map((pane, index) => ({
            name: pane.querySelector('input[name="passengers[' + index + '][name]"]').value,
            birthday: pane.querySelector('input[name="passengers[' + index + '][birthday]"]').value,
            nationality: pane.querySelector('select[name="passengers[' + index + '][nationality]"]').value,
            type_document: pane.querySelector('select[name="passengers[' + index + '][type_document]"]').value,
            data_document: pane.querySelector('input[name="passengers[' + index + '][data_document]"]').value
        }))
    };

    form.innerHTML = `
        <h3>Estamos procesando su solicitud...
        <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
        </div>
        </h3>
        `;

    form.appendChild(createInputHidden('_gitnonce', body.nonce));

    form.appendChild(createInputHidden('product', body.product));
    form.appendChild(createInputHidden('pax[kid]', body.pax.kid));
    form.appendChild(createInputHidden('pax[rpm]', body.pax.rpm));
    form.appendChild(createInputHidden('pax[extra]', body.pax.extra));
    form.appendChild(createInputHidden('pax[standard]', body.pax.standard));
    form.appendChild(createInputHidden('goes[date_trip]', body.goes.date_trip));
    form.appendChild(createInputHidden('goes[id_route]', body.goes.id_route));
    form.appendChild(createInputHidden('goes[id_transport]', body.goes.id_transport));
    form.appendChild(createInputHidden('returns[date_trip]', body.returns.date_trip));
    form.appendChild(createInputHidden('returns[id_route]', body.returns.id_route));
    form.appendChild(createInputHidden('returns[id_transport]', body.returns.id_transport));
    body.passengers.forEach((passenger, index) => {
        form.appendChild(createInputHidden(`passengers[${index}][name]`, passenger.name));
        form.appendChild(createInputHidden(`passengers[${index}][birthday]`, passenger.birthday));
        form.appendChild(createInputHidden(`passengers[${index}][nationality]`, passenger.nationality));
        form.appendChild(createInputHidden(`passengers[${index}][type_document]`, passenger.type_document));
        form.appendChild(createInputHidden(`passengers[${index}][data_document]`, passenger.data_document));
    });
    if (body.round_trip) form.appendChild(createInputHidden('round_trip', 'on'));
    if (body.flexible) form.appendChild(createInputHidden('flexible', 'on'));
});

window.CentralTickets.formProduct = {
    toggleOverlay: toggleOverlay,
    handleMessageModal: handleMessageModal
}

document.querySelector('.woocommerce-product-gallery').remove();
document.querySelector('.summary.entry-summary').classList.add('w-100');
