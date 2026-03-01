let subtotal = 0;
let services = 0;
let tripDataGoes = {
    transport: undefined,
    route: undefined
};
let tripDataReturns = {
    transport: undefined,
    route: undefined
};

const KID = 'kid';
const RPM = 'rpm';
const EXTRA = 'extra';
const STANDARD = 'standard';
const FLEXIBLE = 'flexible';
const TERMS_CONDITIONS = 'terms_conditions';

const counterKid = document.getElementById(dataTransport.elements.idCounters.idKid);
const counterRPM = document.getElementById(dataTransport.elements.idCounters.idRPM);
const counterExtra = document.getElementById(dataTransport.elements.idCounters.idExtra);
const buttonNextTransports = document.getElementById(dataTransport.elements.idButtonNext);
const buttonPrevTransports = document.getElementById(dataTransport.elements.idButtonPrev);
const originLabelGoes = document.getElementById(dataTransport.elements.idOriginLabelGoes);
const destinyLabelGoes = document.getElementById(dataTransport.elements.idDestinyLabelGoes);
const counterStandard = document.getElementById(dataTransport.elements.idCounters.idStandard);
const dateTripLabelGoes = document.getElementById(dataTransport.elements.idDateTripLabelGoes);
const carouselTransportsGoes = document.getElementById(dataTransport.elements.idCarouselGoes);
const originLabelReturns = document.getElementById(dataTransport.elements.idOriginLabelReturns);
const destinyLabelReturns = document.getElementById(dataTransport.elements.idDestinyLabelReturns);
const carouselTransportsReturns = document.getElementById(dataTransport.elements.idCarouselReturns);
const dateTripLabelReturns = document.getElementById(dataTransport.elements.idDateTripLabelReturns);
const transportsContainerGoes = document.getElementById(dataTransport.elements.idTransportsContainerGoes);
const transportsContainerReturns = document.getElementById(dataTransport.elements.idTransportsContainerReturns);
const transportOptionContainerGoes = document.getElementById(dataTransport.elements.idTransportsOptionsContainerGoes);
const transportOptionContainerReturns = document.getElementById(dataTransport.elements.idTransportsOptionsContainerReturns);
const issuesControl = document.querySelectorAll('.control-issue');
const flexibleCheckbox = document.querySelector('input[name="flexible"]');
const termsConditionsCheckbox = document.querySelector('input[name="terms_conditions"]');

function formatShortDate(dateStr) {
    const meses = ['ene.', 'feb.', 'mar.', 'abr.', 'may.', 'jun.', 'jul.', 'ago.', 'sep.', 'oct.', 'nov.', 'dic.'];
    const [year, month, day] = dateStr.split('-');
    return `${parseInt(day, 10)} ${meses[parseInt(month, 10) - 1]} ${year}`;
}

function formatShortHour(hourStr) {
    const [hour, minute] = hourStr.split(':');
    const h = parseInt(hour, 10);
    const suffix = h < 12 ? 'am' : 'pm';
    const displayHour = h === 0 ? 12 : (h > 12 ? h - 12 : h);
    return `${displayHour.toString().padStart(2, '0')}:${minute} ${suffix}`;
}

async function initPaneTransport() {
    if (transportsContainerReturns) {
        transportsContainerReturns.style.display = 'none';
    }
    const switched = window.CentralTickets.formProduct.switched();
    const dates = window.CentralTickets.formProduct.getDate();
    dateTripLabelGoes.textContent = formatShortDate(dates.goes);
    originLabelGoes.textContent = switched ? dataRoute.destiny : dataRoute.origin;
    destinyLabelGoes.textContent = switched ? dataRoute.origin : dataRoute.destiny;
    window.GIT_Counter.setMaximunCombine(dataTransport.maximumPersons, [counterStandard, counterKid, counterRPM]);
    window.GIT_Counter.setMaximunCombine(dataTransport.maximumExtras, [counterExtra]);
    document.querySelectorAll('.counter-control').forEach(control => {
        control.addEventListener('click', () => {
            updatePrice();
        });
    });
    const transportsGoes = await fetchTransports(
        !window.CentralTickets.formProduct.switched() ? dataRoute.origin : dataRoute.destiny,
        !window.CentralTickets.formProduct.switched() ? dataRoute.destiny : dataRoute.origin,
        dateScheduleGoesInput.value
    );
    createTransportOptionPanel(transportsGoes, transportOptionContainerGoes, tripDataGoes, carouselTransportsGoes);
    if (window.CentralTickets.formProduct.getTypeWay() === DOUBLE_WAY) {
        transportsContainerReturns.style.display = 'block';
        dateTripLabelReturns.textContent = formatShortDate(dates.returns);
        originLabelReturns.textContent = switched ? dataRoute.origin : dataRoute.destiny;
        destinyLabelReturns.textContent = switched ? dataRoute.destiny : dataRoute.origin;
        const transportsReturns = await fetchTransports(
            !window.CentralTickets.formProduct.switched() ? dataRoute.destiny : dataRoute.origin,
            !window.CentralTickets.formProduct.switched() ? dataRoute.origin : dataRoute.destiny,
            dateScheduleReturnsInput.value
        );
        createTransportOptionPanel(transportsReturns, transportOptionContainerReturns, tripDataReturns, carouselTransportsReturns);
    } else {
        if (transportsContainerReturns) {
            transportsContainerReturns.style.display = 'none';
        }
    }
}

function calculatePriceService() {
    services = 0;
    tripDataGoes.transport.services.forEach(service => {
        services += service.price;
    });
    if (window.CentralTickets.formProduct.getTypeWay() === DOUBLE_WAY) {
        if (tripDataReturns.transport === undefined) {
            return;
        }
        tripDataReturns.transport.services.forEach(service => {
            services += service.price;
        });
    }
    services /= 100;
}

function calculatePriceSubtotal() {
    paxKid = window.GIT_Counter.getValue(counterKid);
    paxRPM = window.GIT_Counter.getValue(counterRPM);
    paxExtra = window.GIT_Counter.getValue(counterExtra);
    paxStandard = window.GIT_Counter.getValue(counterStandard);
    subtotal = (paxKid * dataTransport.prices[KID]) +
        (paxRPM * dataTransport.prices[RPM]) +
        (paxExtra * dataTransport.prices[EXTRA]) +
        (paxStandard * dataTransport.prices[STANDARD]);
    if (window.CentralTickets.formProduct.getTypeWay() === DOUBLE_WAY) {
        subtotal *= 2;
    }
    if (flexibleCheckbox.checked) {
        if (window.CentralTickets.formProduct.getTypeWay() === DOUBLE_WAY) {
            subtotal += (2 * dataTransport.prices[FLEXIBLE]);
        } else {
            subtotal += dataTransport.prices[FLEXIBLE];
        }
    }
}

function getPax() {
    return {
        kid: window.GIT_Counter.getValue(counterKid),
        rpm: window.GIT_Counter.getValue(counterRPM),
        extra: window.GIT_Counter.getValue(counterExtra),
        standard: window.GIT_Counter.getValue(counterStandard),
    };
}

function passengersCount() {
    return window.GIT_Counter.getValue(counterKid) + window.GIT_Counter.getValue(counterRPM) + window.GIT_Counter.getValue(counterStandard);
}

function updatePrice() {
    calculatePriceService();
    calculatePriceSubtotal();
    document.getElementById(dataTransport.elements.idServicesAmountLabel).textContent = '$' + services.toFixed(2).replace('.', ',');
    document.getElementById(dataTransport.elements.idSubtotalAmountLabel).textContent = '$' + (subtotal).toFixed(2).replace('.', ',');
    document.getElementById(dataTransport.elements.idTotalAmountLabel).textContent = '$' + (services + subtotal).toFixed(2).replace('.', ',');
}

function createTransportOptionPanel(transports, optionContainer, objectKey, carouselContainer) {
    if (transports.length === 0) {
        optionContainer.innerHTML = '<p class="text-center">No hay transportes disponibles para esta ruta.</p>';
        return;
    }
    let index = 0;
    optionContainer.innerHTML = '';
    if (carouselContainer) {
        carouselContainer.querySelector('.carousel-inner').innerHTML = '';
        carouselContainer.querySelector('.carousel-indicators').innerHTML = '';
    }
    for (const transport of transports) {
        if (carouselContainer) {
            const transportDescription = `
            ${transport.services.map(service => service.name).join(' - ')}
            `;
            carouselContainer.querySelector('.carousel-inner').innerHTML += `
                <div class="carousel-item ${index === 0 ? 'active' : ''}" data-transport="${transport.nicename}">
                    <img src="${transport.photo}" class="git-transport-caption" alt="${transport.nicename}">
                    <div class="carousel-caption d-none d-md-block">
                        <h5>${transport.nicename}</h5>
                        <p>${transportDescription}</p>
                    </div>
                </div>
        `;
            carouselContainer.querySelector('.carousel-indicators').innerHTML += `
                <button type="button" data-bs-target="#${carouselContainer.id}" data-bs-slide-to="${index}" class="${index === 0 ? 'active' : ''}" aria-current="${index === 0 ? 'true' : 'false'}" aria-label="${transport.nicename}"></button>
        `;
        }
        const route = transport.routes[0];
        const option = document.createElement('div');
        option.classList.add('option', 'mb-1');
        if (index === 0) {
            option.classList.add('option-selected');
            objectKey.route = route;
            objectKey.transport = transport;
        }
        let icons = '';
        transport.services.forEach(service => {
            icons += `<img width="24"
                            src="${service.icon}"
                            alt="${service.name}"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            data-bs-custom-class="custom-tooltip"
                            data-bs-title="${service.name}">`;
        });
        let right = '<div class="right">' + transport.nicename + '</div>';
        let center = '<div class="center">' + icons + '</div>';
        let left = '<div class="left">' + formatShortHour(route.departure_time) + '</div>';
        option.innerHTML = '<div class="third-container">' + left + center + right + '</div>';
        optionContainer.appendChild(option);
        option.addEventListener('click', () => {
            if (carouselContainer) {
                carouselContainer.querySelectorAll('button[aria-label="' + transport.nicename + '"]').forEach(btn => btn.click());
            }
            objectKey.route = route;
            objectKey.transport = transport;
            optionContainer.querySelectorAll('.option').forEach(opt => {
                if (opt !== option) {
                    opt.classList.remove('option-selected');
                }
            });
            option.classList.add('option-selected');
            updatePrice();
        });
        updatePrice();
        index++;
    }
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
}

async function fetchTransports(zoneOrigin, zoneDestiny, schedule) {
    const urlAjaxFetch = dataTransport.ajaxUrl + '?action=' + dataTransport.hookFetchTransports;
    const fetchData = await fetch(urlAjaxFetch, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            name_zone_origin: zoneOrigin,
            name_zone_destiny: zoneDestiny,
            schedule: schedule,
            split_alias: dataTransport.splitByAlias === '1' ? '1' : '0',
        }),
    });
    const json = await fetchData.json();
    return json.data;
}

document.addEventListener('DOMContentLoaded', () => {
    window.CentralTickets.formProduct.initPaneTransport = initPaneTransport;

    buttonPrevTransports.addEventListener('click', () => {
        document.getElementById('git-form-product-transport').style.display = 'none';
        document.getElementById('git-form-product-route').style.display = 'block';
        tripDataGoes = {
            transport: undefined,
            route: undefined,
        };
        tripDataReturns = {
            transport: undefined,
            route: undefined,
        };
    });

    buttonNextTransports.addEventListener('click', () => {
        if (!termsConditionsCheckbox.checked) {
            window.CentralTickets.formProduct.handleMessageModal('Acepte los términos y condiciones.');
            return;
        }
        if (passengersCount() <= 0) {
            window.CentralTickets.formProduct.handleMessageModal('Debe seleccionar al menos un pasajero.');
            return;
        }
        if (tripDataGoes.transport === undefined || tripDataGoes.route === undefined) {
            window.CentralTickets.formProduct.handleMessageModal('Debe seleccionar un transporte de ida.');
            return;
        }
        if (window.CentralTickets.formProduct.getTypeWay() === DOUBLE_WAY) {
            if (tripDataReturns.transport === undefined || tripDataReturns.route === undefined) {
                window.CentralTickets.formProduct.handleMessageModal('Debe seleccionar un transporte de vuelta.');
                return;
            }
        }
        window.CentralTickets.formProduct.toggleOverlay(true);
        window.CentralTickets.formProduct.initPanePassenger().then(() => {
            document.getElementById('git-form-product-transport').style.display = 'none';
            document.getElementById('git-form-product-passengers').style.display = 'block';
            window.CentralTickets.formProduct.toggleOverlay(false);
        });
    });

    flexibleCheckbox.addEventListener('change', () => {
        const parent = flexibleCheckbox.parentNode.parentNode;
        if (flexibleCheckbox.checked) {
            parent.classList.add('bg-success', 'rounded', 'text-dark', 'bg-opacity-25');
        } else {
            parent.classList.remove('bg-success', 'rounded', 'text-dark', 'bg-opacity-25');
        }
        updatePrice();
    });

    issuesControl.forEach(control => {
        control.addEventListener('click', () => {
            window.CentralTickets.formProduct.handleMessageModal(dataTransport.issues[control.dataset.target]);
        });
    });

    flexibleCheckbox.dispatchEvent(new Event('change'));

    window.CentralTickets.formProduct.passengersCount = passengersCount;
    window.CentralTickets.formProduct.getPax = getPax;
    window.CentralTickets.formProduct.getTransports = () => {
        return {
            goes: tripDataGoes.transport,
            returns: tripDataReturns.transport,
        };
    };
    window.CentralTickets.formProduct.getRoutes = () => {
        return {
            goes: tripDataGoes.route,
            returns: tripDataReturns.route,
        };
    };
});

