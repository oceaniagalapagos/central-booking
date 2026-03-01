import { createOptionPane } from "../options.js";
import { createThirdContainer } from "./create-third-container.js";

export async function createOptionsContainer(zoneOriginName, zoneDestinyName, callback) {
    const routes = await queryRoutes(zoneOriginName, zoneDestinyName);
    return createOptionPane(routes,
        route => {
            const thirdPane = createThirdContainer(route.origin.name, '', route.destiny.name);
            return thirdPane.innerHTML;
        },
        route => callback(route));
}

export async function queryRoutes(zoneOrigin, zoneDestiny) {
    const url = `${domain}routes?name_zone_origin=${zoneOrigin}&name_zone_destiny=${zoneDestiny}`;
    const response = await fetch(url);
    return response.json();
}
