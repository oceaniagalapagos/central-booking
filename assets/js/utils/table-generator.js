export function create_table(data) {
    let table = document.createElement('table');
    table.classList.add('table', 'table-bordered');
    for (let i = 0; i < data.length; i++) {
        let tr = document.createElement('tr');
        for (let j = 0; j < data[i].length; j++) {
            let td = document.createElement('td');
            if (data[i][j] instanceof HTMLElement)
                td.appendChild(data[i][j]);
            else
                td.textContent = data[i][j];
            tr.appendChild(td);
        }
        table.appendChild(tr);
    }
    return table;
}
