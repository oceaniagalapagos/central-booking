const form = document.getElementById(OperatorsLinkData.elements.idForm);
const buttonSubmit = form.querySelector('button[type="submit"]');
const infoPanel = document.getElementById('info-panel');

form.addEventListener('submit', function (event) {
    const originalText = buttonSubmit.textContent;
    buttonSubmit.textContent = 'Generando...';
    buttonSubmit.disabled = true;
    event.preventDefault();
    const formData = new FormData(form);
    fetch(OperatorsLinkData.ajaxUrl, {
        method: 'POST',
        body: formData,
    }).then(response => response.json()).then(data => {
        buttonSubmit.textContent = originalText;
        buttonSubmit.disabled = false;
        if (data.success) {
            infoPanel.innerHTML = '<div class="notice notice-success"><p>Vínculo creado con éxito.</p></div>';
            document.getElementById('id_connector_key_container').innerHTML = data.data;
            document.getElementById('row_connector_key').style.display = '';
        } else {
            infoPanel.innerHTML = '<div class="notice notice-error"><p>Error al crear el vínculo.</p></div>';
        }
    });
});

document.getElementById('copy_connector_key').addEventListener('click', function () {
    const codeContainer = document.getElementById('id_connector_key_container');
    const text = codeContainer.textContent || codeContainer.innerText;
    navigator.clipboard.writeText(text).then(function () {
        document.getElementById('copy_connector_key').textContent = '¡Copiado!';
    }, function (err) {
        console.error('Error al copiar al portapapeles: ', err);
    });
});