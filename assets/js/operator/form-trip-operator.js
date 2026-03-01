const offsetDays = 7;

const inputs = {
    dateEnd: document.querySelector('input[name="date_to"]'),
    dateStart: document.querySelector('input[name="date_from"]'),
}

inputs.dateStart.addEventListener('change', () => {
    const startDate = new Date(inputs.dateStart.value);
    if (!isNaN(startDate.getTime())) {
        const endDate = new Date(startDate);
        endDate.setDate(startDate.getDate() + offsetDays - 1);
        inputs.dateEnd.value = endDate.toISOString().split('T')[0];
    }
});
