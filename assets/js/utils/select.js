export function showOptions(select) {
    if (!select) return;
    for (const option of select.options) {
        option.style.display = '';
    }
}

export function showOptionByValue(select, value) {
    if (!select) return;
    for (const option of select.options) {
        if (option.value == value) {
            option.style.display = "";
            break;
        }
    }
}

export function hiddenOptionByValue(select, value) {
    if (!select) return;
    for (const option of select.options) {
        if (value == option.value) {
            option.style.display = "none";
            break;
        }
    }
}

export function selectOptionByValue(select, value) {
    if (!select) return;
    select.value = value;
}
