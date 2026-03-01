jQuery(document).ready(function ($) {
    console.log(locations_view);

    let pageNumber = 1;
    const $table = $('#table_view_locations tbody');

    $table.on('click', '.delete', function (e) {
        e.preventDefault();
        const location = $(this).data('id');
        deleteEvent(location);
    });

    $('input[name="current_page"]').on('keydown', function (e) {
        if (e.key === 'Enter') {
            const val = $(this).val();
            if (!isNaN(val)) {
                pageNumber = val;
                queryData();
            } else {
                $(this).val(pageNumber);
            }
        }
    });

    function deleteEvent(id) {
        const confirmDelete = confirm(`¿Está seguro de eliminar la ubicación con el ID ${id}? Se eliminarán todas las rutas y tickets relacionadas a esta ubicación.`);
        if (confirmDelete) {
            console.log(id);
            $.ajax({
                type: 'post',
                url: locations_view.url,
                data: {
                    id: id,
                    action: locations_view.hook_remove,
                    security: locations_view.nonce_remove,
                },
                success: function (response) {
                    console.log(response);
                    queryData();
                },
                error: function (response) {
                    console.error(response);
                },
            });
        }
    }

    function queryData() {
        $.ajax({
            type: 'post',
            url: locations_view.url,
            data: {
                page_size: 15,
                page_number: pageNumber,
                action: locations_view.hook_page,
                security: locations_view.nonce_page,
            },
            success: function (response) {
                $table.empty();
                if (response.pagination.current_page == 1) {
                    $('.prev_page').prop("disabled", true)
                    $('.first_page').prop("disabled", true)
                } else {
                    $('.prev_page').prop("disabled", false)
                    $('.first_page').prop("disabled", false)
                }
                if (response.pagination.current_page >= response.pagination.total_pages) {
                    $('.next_page').prop("disabled", true)
                    $('.last_page').prop("disabled", true)
                } else {
                    $('.next_page').prop("disabled", false)
                    $('.last_page').prop("disabled", false)
                }
                $('.total_pages_display').text(response.pagination.total_pages)
                $('.current_page_display').val(response.pagination.current_page)
                $('.current_page_display').text(response.pagination.current_page)
                $('.total_elements_count_display').text(response.pagination.total_elements)
                response.data.forEach(location => {
                    const fila = $(`
                        <tr>
                            <td>
                                <div style="display: flex; flex-direction: column;">
                                    <strong>${location.name}</strong>
                                    <div style="font-size: 12px; color: gray;">
                                        <span><strong>ID:</strong> ${location.id}</span> |
                                        <a href="${locations_view.url_edit.replace('[id_placeholder]', location.id)}" class="edit" data-id="${location.id}">Editar</a> |
                                        <a href="#" class="delete" data-id='${location.id}' style="color: brown;">Eliminar</a>
                                    </div>
                                </div>
                            </td>
                            <td style="display: flex; align-content: center; height: 100%">${location.zone?.name ?? '—'}</td>
                        </tr>
                    `);
                    $table.append(fila);
                });
            }
        });
    }
    queryData();
});
