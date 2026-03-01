jQuery(document).ready(function ($) {
    const form = $('#form-service');
    if (form) {
        form.on('submit', function (event) {
            event.preventDefault();
            $.ajax({
                url: formService.url + '?action=' + formService.hook,
                method: 'post',
                data: {
                    id: $('input[name="id"]').val(),
                    icon: $('input[name="icon"]').val(),
                    name: $('input[name="name"]').val(),
                    price: $('input[name="price"]').val(),
                    transports : window.MultiselectAPI.getSelected($('select[name="transport"]')[0]),
                },
                success: function (response) {
                    location.replace(formService.successRedirect);
                    console.log('Form submitted successfully:', response);
                },
                error: function (error) {
                    console.error('Error submitting form:', error);
                }
            });
        });
    } else {
        console.error('Form not found');
    }
});