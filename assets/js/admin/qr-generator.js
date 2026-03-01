jQuery(document).ready(function ($) {
    $('#type_qr').on('change', function () {
        const selectedValue = $(this).val();
        $('.qr-panel').hide();
        $('#qr-panel-' + selectedValue).show();
        $('.qr-panel .form-control').each((index, element) => {
            element.required = false;
        });
        $('#qr-panel-' + selectedValue + ' .form-control').each((index, element) => {
            const required = element.dataset.required === 'true';
            element.required = required;
        });
    });
    $('#type_qr').trigger('change');
    $('#qr-generator-form').on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serialize();
        $('#qr-generator-form button').prop('disabled', true).text('Generando...');
        $.ajax({
            url: CentralBookingQRGenerator.ajax_url,
            method: 'POST',
            data: formData + '&action=' + CentralBookingQRGenerator.action,
            success: function (response) {
                $('#qr-generator-form button').prop('disabled', false).text('Generar');
                $('#qr-container').html(response.data.qr_html);
                $('#qr-container').show();
            },
            error: function () {
                $('#qr-generator-form button').prop('disabled', false).text('Generar');
                alert('Error al generar el código QR. Pongase en contacto con el soporte.');
            }
        });
    });
});
