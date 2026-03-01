(function ($) {
    $(document).ready(function () {
        $('.git-row-action-link').each(function (index, element) {
            const $element = $(element);
            $element.on('click', function (event) {
                const target = $element.attr('href');
                const $targetContainer = $(target);
                const parentSelector = $targetContainer.data('parent');
                $(parentSelector)
                    .find('div.git-item-container')
                    .each(function (index, container) {
                        const $container = $(container);
                        if ($container.attr('id') === target.substring(1)) {
                            $container.toggleClass('hidden');
                        } else {
                            $container.addClass('hidden');
                        }
                    });
            });
        });

        $('#import_data_button').on('click', function (e) {
            e.preventDefault();
            const $import = $('.git-import-data');
            const $export = $('.git-export-data');
            const showImport = !$import.is(':visible');

            $import.toggle(showImport);
            if (showImport) {
                $export.hide();
            }
        });

        $('#export_data_button').on('click', function (e) {
            e.preventDefault();
            const $import = $('.git-import-data');
            const $export = $('.git-export-data');
            const showExport = !$export.is(':visible');

            $export.toggle(showExport);
            if (showExport) {
                $import.hide();
            }
        });

        $('.git-export-settings').on('change', function (e) {
            const anyChecked = $('.git-export-settings:checked').length > 0;
            $('#export_data_submit').prop('disabled', !anyChecked);
        });

        $('#git-import-data-form > input[type="file"]').on('change', function (e) {
            const anyFile = this.files && this.files.length > 0;
            $('#git-import-data-form > input[type="submit"]').prop('disabled', !anyFile);
        });

        $('#git-export-data-form').on('submit', function (e) {
            e.preventDefault();
            const $form = $(this);
            const formData = $form.serialize();
            $form.find('span.spinner').toggleClass('is-active', true);
            $form.find('input[type="submit"]').prop('disabled', true);
            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: formData,
                success: function (response) {
                    try {
                        let payload = response.data;
                        if (response && typeof response === 'object' && response.success && response.data !== undefined) {
                            payload = response.data;
                        }

                        const jsonString = (typeof payload === 'string') ? payload : JSON.stringify(payload, null, 2);

                        const blob = new Blob([jsonString], { type: 'text/plain' });
                        const url = URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        const filename = 'git-data-export-' + (new Date()).toISOString().slice(0, 19).replace(/[:T]/g, '-') + '.json';
                        a.href = url;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        URL.revokeObjectURL(url);

                        $form.find('span.spinner').toggleClass('is-active', false);
                        $form.find('input[type="submit"]').prop('disabled', false);
                    } catch (err) {
                        console.error('Failed to download export:', err);
                        $form.find('span.spinner').toggleClass('is-active', false);
                        $form.find('input[type="submit"]').prop('disabled', false);
                    }
                },
                error: function (error) {
                    console.error(error);
                    $form.find('span.spinner').toggleClass('is-active', false);
                    $form.find('input[type="submit"]').prop('disabled', false);
                }
            });
        });
    });
})(jQuery);