const form = document.getElementById('git_trip_form');
const buttonLaunchModalInfo = document.querySelectorAll('.button-launch-modal-info');

buttonLaunchModalInfo.forEach(button => {
    button.addEventListener('click', function () {
        form.querySelector('input[name="date_trip"]').value = this.dataset.dateTrip;

        document.getElementById('cell-date-trip').textContent = this.dataset.dateTripDisplay;
        document.getElementById('cell-passengers-count').textContent = this.dataset.passengerCounter;
    });
});
