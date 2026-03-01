let switched = false;
let typeWay = dataRoute.typeWay;

const ONE_WAY = 'one_way';
const DOUBLE_WAY = 'double_way';
const MORNING = 'morning';
const AFTERNOON = 'afternoon';

const buttonNext = document.getElementById(dataRoute.elements.idButtonNext);
const originLabel = document.getElementById(dataRoute.elements.idOriginLabel);
const radioOneWay = document.getElementById(dataRoute.elements.idRadioOneWay);
const destinyLabel = document.getElementById(dataRoute.elements.idDestinyLabel);
const switchButton = document.getElementById(dataRoute.elements.idSwitchButton);
const radioDoubleWay = document.getElementById(dataRoute.elements.idRadioDoubleWay);
const containerTripGoes = document.getElementById(dataRoute.elements.idContainerTripGoes);
const containerTripReturns = document.getElementById(dataRoute.elements.idContainerTripReturns);

const dateTripGoesInput = document.getElementById(dataRoute.elements.idDateTripGoes);
const dateTripReturnsInput = document.getElementById(dataRoute.elements.idDateTripReturns);
const dateScheduleGoesInput = document.getElementById(dataRoute.elements.idScheduleGoes);
const dateScheduleReturnsInput = document.getElementById(dataRoute.elements.idScheduleReturns);

function validateTrip() {
    const goes = dateTripGoesInput ? dateTripGoesInput.value : null;
    const returns = dateTripReturnsInput ? dateTripReturnsInput.value : null;
    const scheduleGoes = dateScheduleGoesInput ? dateScheduleGoesInput.value : null;
    const scheduleReturns = dateScheduleReturnsInput ? dateScheduleReturnsInput.value : null;

    if (!goes) {
        return false;
    }

    if (typeWay === ONE_WAY) {
        return true;
    }

    if (typeWay === DOUBLE_WAY) {
        if (!returns) {
            return false;
        }

        const dateGoes = new Date(goes);
        const dateReturns = new Date(returns);

        if (dateReturns < dateGoes) {
            return false;
        }

        if (dateGoes.getTime() === dateReturns.getTime()) {
            if (scheduleGoes !== MORNING || scheduleReturns !== AFTERNOON) {
                return false;
            }
        }
    }

    return true;
}

function getDates() {
    return {
        goes: dateTripGoesInput ? dateTripGoesInput.value : null,
        returns: dateTripReturnsInput ? dateTripReturnsInput.value : null,
    };
}

function getSchedule() {
    return {
        goes: dateScheduleGoesInput ? dateScheduleGoesInput.value : null,
        returns: dateScheduleReturnsInput ? dateScheduleReturnsInput.value : null
    };
}

function syncDateConstraints() {
    if (typeWay === ONE_WAY) {
        if (dateTripGoesInput) dateTripGoesInput.removeAttribute('max');
        if (dateTripReturnsInput) dateTripReturnsInput.removeAttribute('min');
    } else if (typeWay === DOUBLE_WAY) {
        const goes = dateTripGoesInput ? dateTripGoesInput.value : null;
        const returns = dateTripReturnsInput ? dateTripReturnsInput.value : null;
        if (dateTripReturnsInput && goes) {
            dateTripReturnsInput.min = goes;
            if (returns && returns < goes) dateTripReturnsInput.value = goes;
        }
        if (dateTripGoesInput && returns) {
            dateTripGoesInput.max = returns;
            if (goes && goes > returns) dateTripGoesInput.value = returns;
        }
    }
}

if (dateTripGoesInput) dateTripGoesInput.addEventListener('change', syncDateConstraints);
if (dateTripReturnsInput) dateTripReturnsInput.addEventListener('change', syncDateConstraints);

document.addEventListener('DOMContentLoaded', () => {

    if (switchButton) {
        switchButton.addEventListener('click', () => {
            originLabel.textContent = switched ? dataRoute.origin : dataRoute.destiny;
            destinyLabel.textContent = switched ? dataRoute.destiny : dataRoute.origin;
            switched = !switched;
        });
    }

    if (radioOneWay) {
        radioOneWay.addEventListener('click', () => {
            typeWay = ONE_WAY;
            if (containerTripReturns) {
                containerTripReturns.style.display = 'none';
            }
            syncDateConstraints();
        });
        radioOneWay.click();
    }

    if (radioDoubleWay) {
        radioDoubleWay.addEventListener('click', () => {
            typeWay = DOUBLE_WAY;
            if (containerTripReturns) {
                containerTripReturns.style.display = 'block';
            }
            syncDateConstraints();
        });
    }

    buttonNext.addEventListener('click', () => {
        if (!validateTrip()) {
            window.CentralTickets.formProduct.handleMessageModal('Fechas - horarios de Ida y Vuelta no pueden coincidir.');
            return;
        }
        window.CentralTickets.formProduct.toggleOverlay(true);
        window.CentralTickets.formProduct.initPaneTransport().then(() => {
            document.getElementById('git-form-product-route').style.display = 'none';
            document.getElementById('git-form-product-transport').style.display = 'block';
            window.CentralTickets.formProduct.toggleOverlay(false);
        });
    });

    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    window.CentralTickets.formProduct.toggleOverlay(false);
    window.CentralTickets.formProduct.switched = () => switched;
    window.CentralTickets.formProduct.getTypeWay = () => typeWay;
    window.CentralTickets.formProduct.getDate = getDates;
    window.CentralTickets.formProduct.getSchedule = getSchedule;
});
