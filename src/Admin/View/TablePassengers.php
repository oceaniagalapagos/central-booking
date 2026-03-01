<?php
namespace CentralBooking\Admin\View;

use CentralBooking\Admin\AdminRouter;
use CentralBooking\Admin\Form\FormTransfer;
use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\Data\Passenger;
use CentralBooking\Data\Repository\ResultSetInterface;
use CentralBooking\GUI\AccordionComponent;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\PaginationComponent;
use CentralBooking\GUI\TextComponent;
use CentralBooking\Implementation\GUI\TypeDocumentSelect;

final class TablePassengers implements DisplayerInterface
{
    /**
     * @var ResultSetInterface<Passenger>
     */
    private ResultSetInterface $resultSet;
    public const NONCE_ACTION = 'git_passengers_table_action';

    public function __construct()
    {
        $this->resultSet = $this->fetchTransports();
    }

    private function fetchTransports()
    {
        $pageNumber = isset($_GET['page_number']) ? (int) $_GET['page_number'] : 1;
        $pageSize = isset($_GET['page_size']) ? (int) $_GET['page_size'] : 10;
        $filter = [];

        foreach ($_GET as $key => $value) {
            $filter[$key] = $value;
        }

        $args = array_merge($filter, [
            'order_by' => $this->get_current_order_by(),
            'order' => $this->get_current_order(),
            'limit' => $pageSize,
            'offset' => ($pageNumber - 1) * $pageSize,
            'ticket_status_not' => TicketStatus::PERORDER,
        ]);

        return git_passengers_result_set($args);
    }

    private function filter_pad()
    {
        $accordion = new AccordionComponent();
        $accordion_title = new TextComponent('span');
        $accordion_title->append(git_string_to_component('<i class="bi bi-sliders"></i>'));
        $accordion_title->append(' Filtro');
        $accordion->addItem($accordion_title, $this->filter_form());
        return $accordion;
    }

    private function filter_form()
    {
        $name_filter = git_input_field([
            'name' => 'name',
            'value' => $_GET['name'] ?? ''
        ]);
        $ticket_status_filter = git_ticket_status_select_field('status');
        $type_document_filter = (new TypeDocumentSelect(name: 'type_document'))->create();
        $data_document_filter = git_input_field([
            'name' => 'data_document',
            'value' => $_GET['data_document'] ?? ''
        ]);
        $date_trip_filter = git_input_field([
            'name' => 'date_trip',
            'type' => 'date',
            'value' => $_GET['date_trip'] ?? ''
        ]);
        $served_filter = git_select_field([
            'name' => 'served',
            'value' => $_GET['served'] ?? '',
            'options' => [
                'Seleccione...' => '',
                'Sí' => 'true',
                'No' => 'false'
            ]
        ]);
        $approve_filter = git_select_field([
            'name' => 'approved',
            'value' => $_GET['approved'] ?? '',
            'options' => [
                'Seleccione...' => '',
                'Sí' => 'true',
                'No' => 'false'
            ]
        ]);
        $flexible_filter = git_select_field([
            'name' => 'ticket_flexible',,
            'value' => $_GET['ticket_flexible'] ?? '',
            'options' => [
                'Seleccione...' => '',
                'Sí' => 'true',
                'No' => 'false'
            ]
        ]);
        $nationality_filter = git_country_select_field('nationality');
        $transport_filter = git_transport_select_field('id_transport');
        $origin_filter = git_location_select_field('id_origin');
        $destiny_filter = git_location_select_field('id_destiny');

        $ticket_status_filter->setValue($_GET['status'] ?? '');
        $type_document_filter->setValue($_GET['type_document'] ?? '');
        $nationality_filter->setValue($_GET['nationality'] ?? '');
        $transport_filter->setValue($_GET['id_transport'] ?? '');
        $origin_filter->setValue($_GET['id_origin'] ?? '');
        $destiny_filter->setValue($_GET['id_destiny'] ?? '');

        ob_start();
        ?>
        <form method="GET" action="">
            <input type="hidden" name="page" value="<?= esc_attr($_GET['page'] ?? 'git_passengers') ?>">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <?php $name_filter->getLabel('Nombre')->render() ?>
                    </th>
                    <td>
                        <?php $name_filter->render(); ?>
                    </td>
                    <th scope="row">
                        <?php $nationality_filter->getLabel('Nacionalidad')->render() ?>
                    </th>
                    <td>
                        <?php $nationality_filter->render(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $approve_filter->getLabel('Aprobado')->render() ?>
                    </th>
                    <td>
                        <?php $approve_filter->render(); ?>
                    </td>
                    <th scope="row">
                        <?php $served_filter->getLabel('Transportado')->render() ?>
                    </th>
                    <td>
                        <?php $served_filter->render(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $type_document_filter->getLabel('Tipo de Documento')->render(); ?>
                    </th>
                    <td>
                        <?php $type_document_filter->render(); ?>
                    </td>
                    <th scope="row">
                        <?php $data_document_filter->getLabel('Número de Documento')->render(); ?>
                    </th>
                    <td>
                        <?php $data_document_filter->render(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $date_trip_filter->getLabel('Fecha del viaje')->render(); ?>
                    </th>
                    <td>
                        <?php $date_trip_filter->render(); ?>
                    </td>
                    <th scope="row">
                        <?php $ticket_status_filter->getLabel('Estado del ticket')->render(); ?>
                    </th>
                    <td>
                        <?php $ticket_status_filter->render(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $flexible_filter->getLabel('Flexible')->render(); ?>
                    </th>
                    <td>
                        <?php $flexible_filter->render(); ?>
                    </td>
                    <th scope="row">
                        <?php $transport_filter->getLabel('Transporte')->render(); ?>
                    </th>
                    <td>
                        <?php $transport_filter->render(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $origin_filter->getLabel('Origen')->render(); ?>
                    </th>
                    <td>
                        <?php $origin_filter->render(); ?>
                    </td>
                    <th scope="row">
                        <?php $destiny_filter->getLabel('Destino')->render(); ?>
                    </th>
                    <td>
                        <?php $destiny_filter->render(); ?>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" class="button-primary">Aplicar</button>
                <button type="reset" class="button">Limpiar</button>
            </p>
        </form>
        <?php
        $result = ob_get_clean();
        return git_string_to_component($result);
    }

    private function get_current_order_by()
    {
        $order_by = $_GET['order_by'] ?? 'date_trip';
        return in_array(
            $order_by,
            ['name', 'nationality', 'type_document', 'data_document', 'served', 'approved', 'date_trip', 'date_birth', 'id_ticket', 'id_route', 'id_transport', 'ticket_status'],
        ) ? $order_by : 'date_trip';
    }

    private function get_current_order()
    {
        $order = $_GET['order'] ?? 'DESC';
        return $order === 'DESC' ? 'DESC' : 'ASC';
    }

    private function create_order_link(string $order_by, string $order)
    {
        return add_query_arg([
            'order_by' => $order_by,
            'order' => $order
        ]);
    }

    public function render()
    {
        $pagination = new PaginationComponent();
        $pagination->setData(
            $this->resultSet->getTotalItems(),
            $this->resultSet->getCurrentPage(),
            $this->resultSet->getTotalPages()
        );
        $pagination->setLinks(
            link_first: add_query_arg(['page_number' => 1]),
            link_last: add_query_arg(['page_number' => $this->resultSet->getTotalPages()]),
            link_next: add_query_arg(['page_number' => $this->resultSet->getCurrentPage() + 1]),
            link_prev: add_query_arg(['page_number' => $this->resultSet->getCurrentPage() - 1])
        );
        $this->filter_pad()->render();
        ?>
        <div style="overflow-x: auto; max-width: 1500px">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 5px;" scope="col"></th>
                        <th scope="col" style="width: 400px;"
                            class="manage-column <?= $this->get_current_order_by() === 'name' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('name', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Nombre</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                        <th scope="col" style="width: 100px;"
                            class="manage-column <?= $this->get_current_order_by() === 'nationality' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('nationality', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Nacionalidad</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                        <th scope="col" style="width: 100px;"
                            class="manage-column <?= $this->get_current_order_by() === 'type_document' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('type_document', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Tipo de Documento</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                        <th scope="col" style="width: 100px;"
                            class="manage-column <?= $this->get_current_order_by() === 'data_document' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('data_document', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Número Documento</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                        <th scope="col" style="width: 100px;"
                            class="manage-column <?= $this->get_current_order_by() === 'served' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('served', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Transportado</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                        <th scope="col" style="width: 100px;"
                            class="manage-column <?= $this->get_current_order_by() === 'approved' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('approved', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Aprobado</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->resultSet->getItems() as $passenger): ?>
                        <tr>
                            <td>
                                <input type="checkbox" <?= $passenger->canTransfer() === false ? 'disabled' : '' ?>
                                    data-id="<?= $passenger->id ?>" data-spinner-id="spinner-input-<?= $passenger->id ?>"
                                    data-nonce="<?= git_create_nonce() ?>" class="checkbox-transfer"
                                    id="transfer-check-<?= $passenger->id ?>" <?= FormTransfer::passengerInList($passenger->id) ? 'checked' : '' ?>>
                                <span id="spinner-input-<?= $passenger->id ?>" class="spinner" style="margin: 0;"></span>
                            </td>
                            <td>
                                <label for="transfer-check-<?= $passenger->id ?>">
                                    <?= $passenger->name ?>
                                </label>
                                <div class="row-actions visible">
                                    <span>ID: <?= esc_html($passenger->id) ?></span>
                                    <span> | </span>
                                    <span>
                                        <a class="git-row-action-link" target="_blank"
                                            href="<?= AdminRouter::get_url_for_class(TableTickets::class, ['id' => $passenger->getTicket()->id]) ?>">
                                            Ticket: <?= $passenger->getTicket()->id ?>
                                        </a>
                                    </span>
                                    <span> | </span>
                                    <span>
                                        <a class="git-row-action-link" target="_blank"
                                            href="<?= AdminRouter::get_url_for_class(TablePassengersLog::class, ['id' => $passenger->id]) ?>">
                                            Logs
                                        </a>
                                    </span>
                                    <span> | </span>
                                    <span>
                                        <a class="git-row-action-link" href="#qr-code-container-<?= $passenger->id ?>">
                                            Código <i class="bi bi-qr-code"></i>
                                        </a>
                                    </span>
                                    <span> | </span>
                                    <span>
                                        <a class="git-row-action-link" href="#trip-container-<?= $passenger->id ?>">
                                            Viaje
                                        </a>
                                    </span>
                                </div>
                            </td>
                            <td><?= $passenger->nationality ?></td>
                            <td><?= $passenger->typeDocument ?></td>
                            <td><?= $passenger->dataDocument ?></td>
                            <td><?= $passenger->served ? 'Sí' : 'No' ?></td>
                            <td><?= $passenger->approved ? 'Sí' : 'No' ?></td>
                        </tr>
                        <tr id="actions-container-<?= $passenger->id ?>" class="git-row-actions">
                            <td colspan="7">
                                <div id="trip-container-<?= $passenger->id ?>" class="git-item-container hidden"
                                    data-parent="#actions-container-<?= $passenger->id ?>">
                                    <div class="git-item">
                                        <table style="border-spacing: 20px 3px; border-collapse: separate;">
                                            <thead>
                                                <tr>
                                                    <td colspan="2" style="text-align: center;">Información de viaje</td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><b>Origen:</b></td>
                                                    <td><?= $passenger->getRoute()->getOrigin()->name ?></td>
                                                </tr>
                                                <tr>
                                                    <td><b>Destino:</b></td>
                                                    <td><?= $passenger->getRoute()->getDestiny()->name ?></td>
                                                </tr>
                                                <tr>
                                                    <td><b>Hora:</b></td>
                                                    <td><?= $passenger->getRoute()->getDepartureTime()->pretty() ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><b>Fecha:</b></td>
                                                    <td><?= $passenger->getDateTrip()->pretty() ?></td>
                                                </tr>
                                                <tr>
                                                    <td><b>Transporte:</b></td>
                                                    <td><?= $passenger->getTransport()->nicename ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div id="qr-code-container-<?= $passenger->id ?>" class="git-item-container hidden"
                                    data-parent="#actions-container-<?= $passenger->id ?>">
                                    <div class="git-item">
                                        <div style="padding: 20px; text-align: center;">
                                            <?php $url = git_get_ticket_viewer_qr_url($passenger->getTicket()->id); ?>
                                            <?= git_qr_code(git_qr_data_url($url), ['size' => 250])->compact() ?>
                                        </div>
                                        <a href="<?= $url ?>" target="_blank"><?= $url ?></a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php $pagination->render() ?>
        </div>
        <?php
        $this->scriptTranferList();
    }

    private function scriptTranferList()
    {
        $urlAjax = add_query_arg(
            ['action' => 'git_add_passenger_to_list_transfer'],
            admin_url('admin-ajax.php')
        );
        ?>
        <script>
            jQuery(document).ready(($) => {
                $('.checkbox-transfer').on('change', (checkbox) => {
                    const dataset = checkbox.target.dataset;
                    const $spinner = $(`#${dataset.spinnerId}`);

                    const toggleCheckbox = () => {
                        $spinner.toggleClass('is-active');
                        if ($spinner.hasClass('is-active')) {
                            $(checkbox.target).css('display', 'none');
                        } else {
                            $(checkbox.target).css('display', 'inline-block');
                        }
                    }

                    const passengerId = dataset.id;
                    const nonce = dataset.nonce;
                    const isChecked = checkbox.target.checked;
                    toggleCheckbox();

                    $.ajax({
                        url: '<?= $urlAjax ?>',
                        method: 'POST',
                        data: {
                            id: passengerId,
                            nonce: nonce,
                            add_transfer: isChecked ? 1 : 0
                        },
                        success: (response) => {
                            toggleCheckbox();
                            checkbox.target.checked = response.data.in_list;
                        }
                    });
                });
            });
        </script>
        <?php
    }
}