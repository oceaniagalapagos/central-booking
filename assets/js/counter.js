export const plus_count = (counter, displayer) => {
    const count = document.getElementById(counter);
    const display = document.getElementById(displayer);
    count.value = parseInt(count.value) + 1;
    display.textContent = count.value;
    total_display.textContent = calculate_total_price();
}

export const minus_count = (counter, displayer) => {
    const count = document.getElementById(counter);
    const display = document.getElementById(displayer);
    count.value = parseInt(count.value) - 1;
    display.textContent = count.value;
    total_display.textContent = calculate_total_price();
    if (total_passengers() <= maximun_person['maximun'])
        warning_maximum_passengers_pane.style.display = 'none';
}
