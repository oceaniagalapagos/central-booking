const formsAvailability = document.querySelectorAll('.form-availability');

formsAvailability.forEach(form => {
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(form);

        fetch(gitTransportTable.url, {
            method: 'POST',
            body: formData
        })
            .then(async response => {
                const json = await response.json();
                if (response.ok) {
                    return json;
                }
                console.log(json);
                let errorMessage = '';
                errorMessage += `<div class="notice notice-error">`;
                errorMessage += json.message;
                errorMessage += `</div>`;
                document.getElementById('issues_container').innerHTML = errorMessage;
                throw new Error(json.message || 'Network response was not ok');
            })
            .then(data => {
                location.reload();
                console.log(data);
            })
            .catch(error => {
                console.error(error);
            });
    });
});
