const issuesContainer = document.getElementById('container_issues_to_transfer');
const passengersContainer = document.getElementById('container_passengers_to_transfer');
const form = document.getElementById('git-transfer-form');

function getPassengersToTransfer() {
    return JSON.parse(localStorage.getItem('passengers_to_transfer') || '[]');
}

function savePassengersToTransfer(passengers) {
    localStorage.setItem('passengers_to_transfer', JSON.stringify(passengers));
}

function removePassenger(passengerId) {
    const passengers = getPassengersToTransfer();
    const passengerStr = String(passengerId);

    const filtered = passengers.filter(id => String(id) !== passengerStr);

    if (filtered.length !== passengers.length) {
        savePassengersToTransfer(filtered);
        return true;
    }
    return false;
}

function initRemovePassengerButtons() {
    const links = document.getElementsByClassName('link-remove-passenger');

    for (const link of links) {
        link.addEventListener('click', function () {
            const passengerId = this.dataset.passengerId;
            if (removePassenger(passengerId)) {
                this.closest('tr').remove();
            }
        });
    }
};

form.addEventListener('submit', function (event) {
    localStorage.removeItem('passengers_to_transfer');
    // event.preventDefault();

    // const formData = new FormData(form);

    // jQuery.ajax({
    //     url: form.getAttribute('action'),
    //     method: 'POST',
    //     data: formData,
    //     processData: false,
    //     contentType: false,
    //     success: function (response) {
    //         if (response.success) {
    //             localStorage.removeItem('passengers_to_transfer');
    //             location.replace(gitTransferForm.successRedirect);
    //         } else {
    //             showError(`
    //             <div class="notice notice-error is-dismissible">
    //             <p>${response.data.message}</p>
    //             </div>`);
    //         }
    //     },
    //     error: function (response) {
    //         console.log(response);
    //         showError(`
    //         <div class="notice notice-error is-dismissible">
    //             <p>Ha ocurrido un error a la hora de trasladar a los pasajeros.</p>
    //         </div>
    //     `);
    //     }
    // });
});

jQuery.ajax({
    url: gitTransferForm.hook,
    method: 'POST',
    data: {
        passengers: JSON.parse(localStorage.getItem('passengers_to_transfer') ?? '[]')
    },
    beforeSend: function () {
        passengersContainer.innerHTML = '<div class="loading">Cargando...</div>';
    },
    success: function (response) {
        if (response.success) {
            if (response.data.html) {
                passengersContainer.innerHTML = response.data.html;
                initRemovePassengerButtons();
            }
        } else {
            showError(response.data.message || 'Error desconocido');
        }
    },
    error: function () {
        issuesContainer.innerHTML = `
            <div class="notice notice-error is-dismissible">
                <p>Ha ocurrido un error a la hora de buscar a los pasajeros.</p>
            </div>
        `;
    }
});

function showError(message) {
    issuesContainer.innerHTML = `
        <div class="notice notice-warning is-dismissible">
            <p>${message}</p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text">Descartar este aviso.</span>
            </button>
        </div>
    `;
}