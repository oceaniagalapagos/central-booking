/**
 * Obtiene la lista de los países disponibles por el sistema.
 * @returns {Promise<Array<string>>}
 */
export async function countries() {
    const fetchData = await fetch(countriesPath.data);
    return fetchData.json();
}
