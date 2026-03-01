import { prettyDate } from "../utils/formatter.js";
import { print_table_operator } from "./printer.js";
import { create_table } from "./table-generator.js";

export function create_table_display(info, is_super_user) {
    let random_id = getRndInteger(100, 999);
    const container = document.createElement('div');
    const info_pane = document.createElement('div');
    container.id = random_id;
    container.classList.add('operation_content');
    container.style.display = 'none';
    info_pane.style.display = 'flex';
    const info_route_pane = create_data_pane('Ruta', {
        Origen: info['route']['origin'],
        Destino: info['route']['destiny'],
        Fecha: prettyDate(info['route']['date']),
        Hora: info['route']['time'],
    });
    const info_transport_pane = create_data_pane('Transporte', {
        Nombre: info['transport']['nicename'],
        Capitan: info['transport']['captain'],
        Código: info['transport']['code'],
    });
    info_route_pane.style.width = "50%"
    info_transport_pane.style.width = "50%"
    info_pane.appendChild(info_route_pane);
    info_pane.appendChild(info_transport_pane);
    container.appendChild(info_pane);
    container.appendChild(create_table_passengers(info['passengers']));
    container.appendChild(create_button_print(info));
    if (is_super_user) {
        container.appendChild(create_button_completed(info['route']['date']));
    }
    return container;
}

function getRndInteger(min, max) {
    return Math.floor(Math.random() * (max - min)) + min;
}

function create_button_print(info) {
    let button = document.createElement('button');
    button.textContent = 'Imprimir';
    button.classList.add('btn', 'btn-primary');
    button.addEventListener('click', () => print_table_operator(info));
    return button;
}

function create_button_completed(date) {
    let button = document.createElement('button');
    button.textContent = 'Completado';
    button.classList.add('btn', 'btn-success', 'ms-3', 'complete-trip-operator');
    button.setAttribute('date', date);
    return button;
}

function create_table_passengers(passengers) {
    let info = [];
    info[0] = [
        'N°',
        'Nombre',
        'País',
        'Tipo Documento',
        'Número Documento',
        'Contacto'
    ];
    for (let i = 0; i < passengers.length; i++) {
        info[i + 1] = [
            i + 1,
            passengers[i]['name'],
            passengers[i]['nationality'],
            passengers[i]['type_document'],
            passengers[i]['data_document'],
            passengers[i]['phone']
        ]
    }
    return create_table(info);
}

function create_data_pane(title, data) {
    let content = document.createElement('div');
    content.innerHTML = `<h5>${title}</h5><hr>`;

    for (let key in data) {
        if (data.hasOwnProperty(key)) {
            let p = document.createElement('p');
            p.style.marginBottom = 0;
            p.innerHTML = `<b>${key}:</b> ${data[key]}`;
            content.appendChild(p);
        }
    }

    return content;
}