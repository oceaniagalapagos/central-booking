/**
 * Obtiene la lista de los tipos de documento disponibles por el sistema.
 * @returns {Promise<Array<string>>}
 */
export async function documents() {
    const fetchData = await fetch(documentsPath.data);
    return fetchData.json();
}