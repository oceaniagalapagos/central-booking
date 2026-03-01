<?php
namespace CentralBooking\Admin\Form;

use CentralBooking\Admin\AdminRouter;
use CentralBooking\Admin\View\TablePassengers;
use CentralBooking\Admin\View\TableTickets;
use CentralBooking\Data\Passenger;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\GUI\SelectComponent;
use CentralBooking\Implementation\GUI\SelectorRouteCombine;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

final class FormTransfer implements DisplayerInterface
{
    private SelectComponent $origin_select;
    private SelectComponent $destiny_select;
    private SelectComponent $schedule_select;
    private SelectComponent $transport_select;
    private InputComponent $date_trip_input;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $route_combine = new SelectorRouteCombine();
        $this->origin_select = $route_combine->get_origin_select('origin_id');
        $this->destiny_select = $route_combine->get_destiny_select('destiny_id');
        $this->schedule_select = $route_combine->get_time_select('departure_time');
        $this->transport_select = $route_combine->get_transport_select('transport_id');
        $this->date_trip_input = git_date_trip_field('date_trip');

        $this->date_trip_input->setRequired(true);
    }

    public function render()
    {
        // wp_enqueue_script(
        //     'central-tickets-passengers-table',
        //     CENTRAL_BOOKING_URL . '/assets/js/admin/transfer-form.js',
        //     ['jquery'],
        //     time(),
        //     []
        // );
        // wp_localize_script(
        //     'central-tickets-passengers-table',
        //     'gitTransferForm',
        //     [
        //         'hook' => admin_url('admin-ajax.php?action=git_approve_passengers_table'),
        //         'successRedirect' => admin_url('admin.php?page=central_passengers'),
        //     ]
        // );
        $this->showMessage();
        ?>
        <form action="<?= admin_url('admin-ajax.php?action=git_transfer_passengers') ?>" method="post" id="git-transfer-form">
            <table class="form-table">
                <tr>
                    <th><?php $this->origin_select->getLabel('Origen')->render(); ?></th>
                    <td><?php $this->origin_select->render(); ?></td>
                    <th><?php $this->destiny_select->getLabel('Destino')->render(); ?></th>
                    <td><?php $this->destiny_select->render(); ?></td>
                </tr>
                <tr>
                    <th><?php $this->schedule_select->getLabel('Horario')->render(); ?></th>
                    <td><?php $this->schedule_select->render(); ?></td>
                    <th><?php $this->transport_select->getLabel('Transporte')->render(); ?></th>
                    <td><?php $this->transport_select->render(); ?></td>
                </tr>
                <tr>
                    <th><?php $this->date_trip_input->getLabel('Fecha del Viaje')->render(); ?></th>
                    <td><?php $this->date_trip_input->render(); ?></td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 200px;">Nombre</th>
                        <th style="width: 100px;">Nacionalidad</th>
                        <th style="width: 100px;">Tipo de Documento</th>
                        <th style="width: 150px;">Número de Documento</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->getListPassenger() as $passenger): ?>
                        <tr id="passenger-row-<?= $passenger->id ?>">
                            <td>
                                <span>
                                    <input type="hidden" name="passengers[]" value="<?= $passenger->id ?>">
                                    <?= esc_html($passenger->name); ?>
                                    <span id="spinner-input-<?= $passenger->id ?>" class="spinner"
                                        style="margin: 0;"></span>
                                </span>
                                <div class="row-actions visible">
                                    <span>
                                        <a target="_blank"
                                            href="<?= esc_url(AdminRouter::get_url_for_class(TablePassengers::class, ['id' => $passenger->id])); ?>">
                                            ID: <?= esc_html($passenger->id); ?>
                                        </a>
                                    </span>
                                    <span> | </span>
                                    <span>
                                        <a target="_blank"
                                            href="<?= esc_url(AdminRouter::get_url_for_class(TableTickets::class, ['id' => $passenger->getTicket()->id])); ?>">
                                            Ticket: <?= esc_html($passenger->getTicket()->id); ?>
                                        </a>
                                    </span>
                                    <span> | </span>
                                    <span class="trash">
                                        <a class="link-remove-passenger" data-row="<?= "passenger-row-{$passenger->id}" ?>"
                                            data-id="<?= $passenger->id ?>"
                                            data-nonce="<?= git_create_nonce() ?>"
                                            data-spinner="spinner-input-<?= $passenger->id ?>" style="cursor: pointer;">
                                            Quitar de la lista
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </span>
                                </div>
                            </td>
                            <td><?= $passenger->nationality ?></td>
                            <td><?= $passenger->typeDocument ?></td>
                            <td><?= $passenger->dataDocument ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="submit">
                <button type="submit" class="button button-primary">Trasladar</button>
            </p>
        </form>
        <?php
        $urlAjax = add_query_arg(
            ['action' => 'git_add_passenger_to_list_transfer'],
            admin_url('admin-ajax.php')
        );
        ?>
        <script>
            jQuery(document).ready(($) => {
                $('.link-remove-passenger').on('click', (link) => {
                    const dataset = link.target.dataset;
                    const $spinner = $(`#${dataset.spinner}`);
                    $spinner.addClass('is-active');

                    $.ajax({
                        url: '<?= $urlAjax ?>',
                        method: 'POST',
                        data: {
                            id: dataset.id,
                            nonce: dataset.nonce,
                            add_transfer: 0
                        },
                        success: (response) => {
                            if (response.data.in_list === false) {
                                $(`#${dataset.row}`).remove();
                            }
                            $spinner.removeClass('is-active');
                        }
                    });
                });
            });
        </script>
        <?php
    }

    private function showMessage()
    {
        MessageAlert::getInstance(FormTransfer::class)->render();
    }

    public static function writeMessage(string $message, MessageLevel $level = MessageLevel::INFO, int $expiration_seconds = 30)
    {
        (new MessageTemporal)->writeTemporalMessage(
            $message,
            FormTransfer::class,
            $level,
            $expiration_seconds
        );
    }

    private function getListPassenger()
    {
        $key = 'git_list_passengers_to_transfer';
        $service = git_temporal_service();
        $list = $service->read($key) ?? [];
        $passengers = [];
        foreach ($list as $idPassenger) {
            $passenger = git_passenger_by_id($idPassenger);
            if ($passenger === null) {
                continue;
            }
            $passengers[] = $passenger;
        }
        return $passengers;
    }

    public static function passengerInList(Passenger|int $passenger)
    {
        $key = 'git_list_passengers_to_transfer';
        $service = git_temporal_service();
        $list = $service->read($key) ?? [];
        $idPassenger = is_int($passenger) ? $passenger : $passenger->id;
        return in_array($idPassenger, $list);
    }

    public static function removePassengerInList(Passenger|int $passenger)
    {
        $key = 'git_list_passengers_to_transfer';
        $service = git_temporal_service();
        $list = $service->read($key) ?? [];
        $idPassenger = is_int($passenger) ? $passenger : $passenger->id;
        $list = array_filter($list, fn($id) => $id !== $idPassenger);
        $service->write($key, $list, 24 * 3600);
    }

    public static function addListPassenger(Passenger|int $passenger)
    {
        if (self::passengerInList($passenger)) {
            return;
        }
        $key = 'git_list_passengers_to_transfer';
        $service = git_temporal_service();
        $list = $service->read($key) ?? [];
        $idPassenger = is_int($passenger) ? $passenger : $passenger->id;
        $list[] = $idPassenger;
        $service->write($key, $list, 24 * 3600);
    }
}
