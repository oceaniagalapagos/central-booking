if (localStorage.getItem('passengers_to_transfer') === null) {
    localStorage.setItem('passengers_to_transfer', '[]');
}

function getPassengersToTransfer() {
    return JSON.parse(localStorage.getItem('passengers_to_transfer') || '[]');
}

function savePassengersToTransfer(passengers) {
    localStorage.setItem('passengers_to_transfer', JSON.stringify(passengers));
}

function isPassengerSelected(passengerId) {
    const passengers = getPassengersToTransfer();
    return passengers.includes(String(passengerId));
}

function addPassenger(passengerId) {
    const passengers = getPassengersToTransfer();
    const passengerStr = String(passengerId);

    if (!passengers.includes(passengerStr)) {
        passengers.push(passengerStr);
        savePassengersToTransfer(passengers);
        return true;
    }
    return false;
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

document.querySelectorAll('input.transfer-check').forEach(checkbox => {
    checkbox.checked = isPassengerSelected(checkbox.value);
    checkbox.addEventListener('change', function (event) {
        const passengerId = this.value;
        if (this.checked) {
            addPassenger(passengerId);
        } else {
            removePassenger(passengerId);
        }
        document.dispatchEvent(new CustomEvent('passengersSelectionChanged', {
            detail: {
                passengerId: passengerId,
                selected: this.checked,
                allSelected: getPassengersToTransfer()
            }
        }));
    });
});

function clearPassengerSelection() {
    savePassengersToTransfer([]);
    document.querySelectorAll('.transfer-check').forEach(checkbox => {
        checkbox.checked = false;
    });
    console.log('🧹 Selección limpiada');
}

function selectAllPassengers() {
    const allPassengerIds = Array.from(document.querySelectorAll('.transfer-check'))
        .map(checkbox => String(checkbox.value));

    savePassengersToTransfer(allPassengerIds);

    document.querySelectorAll('.transfer-check').forEach(checkbox => {
        checkbox.checked = true;
    });
}

function getSelectedCount() {
    return getPassengersToTransfer().length;
}
