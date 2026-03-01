const buttonCopy = document.querySelectorAll('.button-copy');

buttonCopy.forEach(button => {
    button.addEventListener('click', function () {
        const secretKeyInput = document.getElementById(this.dataset.target);
        navigator.clipboard.writeText(secretKeyInput.value).then(function () {
            button.textContent = '¡Copiado!';
            setTimeout(() => {
                button.textContent = 'Copiar';
            }, 2000);
        }, function (err) {
            alert('Error al copiar la clave secreta: ', err);
        });
    });
});
