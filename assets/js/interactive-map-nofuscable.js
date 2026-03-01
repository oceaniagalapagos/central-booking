const map = L.map('map', {
    minZoom: 9,
    maxZoom: 10
}).setView([-0.7, -90.5], 7);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© Ocean IA Galapagos',
    maxZoom: 10,
    minZoom: 1
}).addTo(map);

const islands = [
    { name: "Isla Isabela - Puerto Villamil", coords: [-0.9574, -90.9658], info: "Muelle Municipal", id: "isabela" },
    { name: "Isla Santa Cruz - Puerto Ayora", coords: [-0.7419, -90.3127], info: "Muelle Turístico", id: "santacruz" },
    { name: "Isla San Cristóbal - Puerto Baquerizo Moreno", coords: [-0.9019, -89.6107], info: "Capital de Galápagos", id: "sancristobal" },
    { name: "Isla Floreana - Puerto Velasco Ibarra", coords: [-1.2856, -90.4688], info: "Muelle Municipal", id: "floreana" }
];

const boatIcon = L.icon({
    iconUrl: CENTRAL_TICKETS_URL + 'assets/img/boat-outline.svg',
    iconSize: [35, 35],
    iconAnchor: [35, 35],
    popupAnchor: [0, -35]
});

const ticketIcon = L.icon({
    iconUrl: CENTRAL_TICKETS_URL + 'assets/img/ticket-outline.svg',
    iconSize: [40, 40],
    iconAnchor: [16, 16],
    popupAnchor: [0, -16],
    className: 'git-ticket-icon'
});

const urlProducts = {
    isabela: 'https://supgalapagos.tours/central/producto/ferry-isabela-santacruz/',
    floreana: 'https://supgalapagos.tours/central/producto/ferry-santacruz-floreana/',
    sancristobal: 'https://supgalapagos.tours/central/producto/ferry-sancristobal-santacruz/',
};

const urlTicketIcons = {
    isabela: CENTRAL_TICKETS_URL + 'assets/img/git_tkt_gray.svg',
    floreana: CENTRAL_TICKETS_URL + 'assets/img/git_tkt_green.svg',
    sancristobal: CENTRAL_TICKETS_URL + 'assets/img/git_tkt_blue.svg',
};

const markers = {};
const circles = {};
const lines = {};
let ticketMarker = null;
let firstIslandSelected = null;
let secondIslandSelected = null;

function clearAll() {
    Object.keys(markers).forEach(id => {
        if (markers[id]) {
            map.removeLayer(markers[id]);
            delete markers[id];
        }
    });

    Object.keys(lines).forEach(id => {
        if (lines[id]) {
            map.removeLayer(lines[id]);
            delete lines[id];
        }
    });

    if (ticketMarker) {
        map.removeLayer(ticketMarker);
        ticketMarker = null;
    }

    firstIslandSelected = null;
    secondIslandSelected = null;
}

islands.forEach(island => {
    const circle = L.circle(island.coords, {
        color: '#4CAF50',
        fillColor: '#90EE90',
        fillOpacity: 0.6,
        weight: 2,
        radius: 4000
    }).addTo(map);

    circles[island.id] = circle;

    circle.on('click', function (e) {
        L.DomEvent.stopPropagation(e);

        if (!firstIslandSelected) {
            firstIslandSelected = island.id;

            const marker = L.marker(island.coords, { icon: boatIcon })
                .addTo(map)
                .bindPopup(`<b>${island.name}</b><br>${island.info}`);

            markers[island.id] = marker;

            if (island.id !== 'santacruz') {
                const santaCruzCoords = islands.find(i => i.id === 'santacruz').coords;
                const line = L.polyline([island.coords, santaCruzCoords], {
                    color: '#FF6B6B',
                    weight: 3,
                    dashArray: '10, 10',
                    opacity: 0.8
                }).addTo(map);

                lines[island.id] = line;
            }
        } else if (!secondIslandSelected && island.id !== firstIslandSelected) {
            if (island.id === 'santacruz' || firstIslandSelected === 'santacruz') {
                secondIslandSelected = island.id;

                const marker = L.marker(island.coords, { icon: boatIcon })
                    .addTo(map)
                    .bindPopup(`<b>${island.name}</b><br>${island.info}`);

                markers[island.id] = marker;

                if (island.id !== 'santacruz') {
                    const santaCruzCoords = islands.find(i => i.id === 'santacruz').coords;
                    const line = L.polyline([island.coords, santaCruzCoords], {
                        color: '#FF6B6B',
                        weight: 3,
                        dashArray: '10, 10',
                        opacity: 0.8
                    }).addTo(map);

                    lines[island.id] = line;
                }

                const firstIsland = islands.find(i => i.id === firstIslandSelected);
                const secondIsland = islands.find(i => i.id === secondIslandSelected);

                if (firstIsland && secondIsland) {
                    const santaCruz = islands.find(i => i.id === 'santacruz');
                    let midLat, midLng;

                    if (firstIslandSelected === 'santacruz') {
                        midLat = (santaCruz.coords[0] + secondIsland.coords[0]) / 2;
                        midLng = (santaCruz.coords[1] + secondIsland.coords[1]) / 2;
                    } else {
                        midLat = (firstIsland.coords[0] + santaCruz.coords[0]) / 2;
                        midLng = (firstIsland.coords[1] + santaCruz.coords[1]) / 2;
                    }

                    ticketMarker = L.marker([midLat, midLng], { icon: ticketIcon })
                        .addTo(map);

                    ticketMarker.on('click', function (e) {
                        L.DomEvent.stopPropagation(e);
                        document.getElementById('ticketModal').style.display = 'block';
                    });
                }
            }

            const img = document.querySelectorAll('img.leaflet-marker-icon');
            const link = document.createElement('a');
            link.target = '_top';
            if (
                firstIslandSelected === 'santacruz' && secondIslandSelected === 'sancristobal' ||
                firstIslandSelected === 'sancristobal' && secondIslandSelected === 'santacruz'
            ) {
                img[2].src = urlTicketIcons.sancristobal;
                img[2].addEventListener('click', function () {
                    link.href = urlProducts.sancristobal;
                    link.click();
                });
            } else if (
                firstIslandSelected === 'isabela' && secondIslandSelected === 'santacruz' ||
                firstIslandSelected === 'santacruz' && secondIslandSelected === 'isabela'
            ) {
                img[2].src = urlTicketIcons.isabela;
                img[2].addEventListener('click', function () {
                    link.href = urlProducts.isabela;
                    link.click();
                });
            } else if (
                firstIslandSelected === 'santacruz' && secondIslandSelected === 'floreana' ||
                firstIslandSelected === 'floreana' && secondIslandSelected === 'santacruz'
            ) {
                img[2].src = urlTicketIcons.floreana;
                img[2].addEventListener('click', function () {
                    link.href = urlProducts.floreana;
                    link.click();
                });
            }

        } else {
            clearAll();
        }
    });
});

map.on('click', function (e) {
    clearAll();
});

const modal = document.getElementById('ticketModal');
const closeBtn = document.querySelector('.close');

map.on('zoom', function () {
    const currentZoom = map.getZoom();
    const center = map.getCenter();

    if (currentZoom > 4) {
        let insideCircle = false;
        islands.forEach(island => {
            const distance = center.distanceTo(island.coords);
            if (distance <= 8000) {
                insideCircle = true;
            }
        });

        if (insideCircle) {
            map.setZoom(4);
        }
    }
});

const bounds = L.latLngBounds(islands.map(i => i.coords));
map.fitBounds(bounds, { padding: [50, 50] });

document.addEventListener('DOMContentLoaded', function () {
    const attribution = document.querySelector('.leaflet-control-attribution');
    attribution.innerHTML = '<a href="mailto:ocean.ia.galapagos@gmail.com">© Ocean IA Galapagos</a>';
});