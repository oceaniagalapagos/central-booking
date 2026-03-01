import { createOptionPane } from "../options.js";
import { prettyTime } from "../utils/formatter.js";
import { createThirdContainer } from "./create-third-container.js";

export let doubleWay = false;

export let transportGoes = undefined;
export let transportRetruns = undefined;

const form = document.getElementById('product_form');
const selectorPane = document.getElementById('product_presentation_selector_transport');

const paneGoes = selectorPane.querySelector('div[target="transports_panel_a"]');
const paneReturns = selectorPane.querySelector('div[target="transports_panel_b"]');

/**
 * 
 * @param {{ idOrigin:number, idDestiny:number, dateTrip:string, type:string }} param0 
 * @returns {Promise<Array<object>>}
 */
async function queryTransport({ idOrigin, idDestiny, dateTrip, type }) {
    const httpReference = form.querySelector('input[name="http-reference"]').value;
    const url = `${httpReference}transports?id_origin=${idOrigin}&id_destiny=${idDestiny}&type=${type}`;
    const response = await fetch(url);
    const transportsRaw = await response.json();
    const transports = [];
    transportsRaw.forEach(raw => {
        transports.push(...transformData(raw));
    });
    return transports.filter(transport => {
        const daySelected = new Date(dateTrip)
            .toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();
        const { date_start, date_end } = transport.maintenance_dates;

        const isInMaintenance = new Date(dateTrip) >= new Date(date_start)
            && new Date(dateTrip) <= new Date(date_end);
        const isWorkingDay = transport.working_days.includes(daySelected);

        const [hours, minutes, seconds] = transport.route.departure_time.split(':').map(Number);
        const departureTotalMinutes = hours * 60 + minutes;

        const isMorning = timeSelect.value === 'morning' && departureTotalMinutes >= 0 && departureTotalMinutes < 720;
        const isAfternoon = timeSelect.value === 'afternoon' && departureTotalMinutes >= 720 && departureTotalMinutes < 1440;

        return !isInMaintenance && isWorkingDay && (isMorning || isAfternoon);
    });
}

/**
 * @param {object} data 
 * @returns {Array}
 */
function transformData(data) {
    return data.routes.map(route => ({
        ...data,
        route,
        routes: undefined
    }));
}

/**
 * 
 * @param {{ idLocationOrigin:number, idLocationDestiny:number, dateTrip:string, type:string }} param0 
 * @returns 
 */
export function init({ idLocationOrigin, idLocationDestiny, dateTrip, type }) {
    queryTransport(idLocationOrigin, idLocationDestiny, dateTrip, type)
        .then(transports => {
            const optionsContainer = createOptionPane(
                transports,
                transport => {
                    let iconsContainer = document.createElement('div');
                    for (const service of transport.services) {
                        iconsContainer.innerHTML += `<img src="${service.icon}" alt="${service.name}" width="20">`;
                    }
                    return createThirdContainer(transport.nicename, iconsContainer, prettyTime(transport.departure_time),);
                },
                transport => transportGoes = transport);
            paneGoes.appendChild(optionsContainer);
        });
    if (!doubleWay) return;
    queryTransport(idLocationDestiny, idLocationOrigin, dateTrip, type)
        .then(transports => {
            const optionsContainer = createOptionPane(
                transports,
                transport => {
                    let iconsContainer = document.createElement('div');
                    for (const service of transport.services) {
                        iconsContainer.innerHTML += `<img src="${service.icon}" alt="${service.name}" width="20">`;
                    }
                    return createThirdContainer(transport.nicename, iconsContainer, prettyTime(transport.departure_time),);
                },
                transport => transportRetruns = transport);
            paneReturns.appendChild(optionsContainer);
        });
}
