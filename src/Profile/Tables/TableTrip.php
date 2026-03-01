<?php
namespace CentralBooking\Profile\Tables;

use CentralBooking\Data\Constants\UserRole;
use CentralBooking\Data\Route;
use CentralBooking\Data\Services\PassengerService;
use CentralBooking\Data\Services\RouteService;
use CentralBooking\Data\Services\TransportService;
use CentralBooking\Data\Transport;
use CentralBooking\GUI\ButtonComponent;
use CentralBooking\GUI\ComponentBuilder;
use CentralBooking\GUI\Constants\ButtonStyleConstants;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\ModalComponent;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;
use DateInterval;
use DatePeriod;
use DateTime;
use Exception;

final class TableTrip implements DisplayerInterface
{
    public const NONCE_ACTION = 'git_trip_operator_nonce';
    private ModalComponent $modal;

    public function __construct()
    {
        $this->modal = new ModalComponent('Control de Viaje');
    }

    public function render()
    {
        if (
            !isset($_GET['time']) ||
            !isset($_GET['date_to']) ||
            !isset($_GET['date_from']) ||
            !isset($_GET['id_origin']) ||
            !isset($_GET['id_destiny']) ||
            !isset($_GET['id_transport'])
        ) {
            return;
        }

        $route = $this->get_route(
            (int) $_GET['id_origin'],
            (int) $_GET['id_destiny'],
            $_GET['time']
        );

        if ($route === null) {
            return;
        }

        $transport = $this->get_transport((int) $_GET['id_transport']);

        if ($transport === null) {
            return;
        }

        if (!git_current_user_has_role(UserRole::ADMINISTRATOR)) {
            if ($transport->getOperator()->getUser()->ID !== get_current_user_id()) {
                ?>
                <p class="text-center">No tienes permiso para realizar esta consulta.</p>
                <?php
                return;
            }
        }

        $dates = $this->obtenerFechasEntre(
            $_GET['date_from'],
            $_GET['date_to']
        );

        $this->modal->set_body_component(ComponentBuilder::create(
            $this->modal_table_content($route, $transport)
        ));

        wp_enqueue_script(
            'git-trip-operator',
            CENTRAL_BOOKING_URL . '/assets/js/operator/table-trip-operator.js',
            [],
            time(),
        );

        wp_localize_script(
            'git-trip-operator',
            'gitTripOperator',
            [
                'url' => admin_url('admin-ajax.php'),
                'hook' => 'git_finish_trip',
                'nonce' => wp_create_nonce('git_trip_operator_nonce')
            ]
        );

        $this->modal->render();
        $this->showMessage();
        ?>
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <?php foreach ($dates as $date): ?>
                        <th style="text-align: center;"> <?= git_date_format($date, true); ?> </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php
                    foreach ($dates as $date):
                        $passengers = $this->get_passengers($transport->id, $route->id, $date);
                        $total_passengers = count($passengers);
                        $path_pdf_trip = '#';
                        $path_pdf_salling_request = '#';
                        ?>
                        <td>
                            <?php
                            $button = new ButtonComponent($total_passengers . ' / ' . $transport->getCapacity());
                            $button->set_style(ButtonStyleConstants::BASE);
                            if ($total_passengers > 0) {
                                $button = $this->modal->create_button_launch($total_passengers . ' / ' . $transport->getCapacity());
                            }
                            $button->attributes->set('data-passenger-counter', $total_passengers . ' / ' . $transport->getCapacity());
                            $button->class_list->add('button-launch-modal-info', 'w-100');
                            $button->attributes->set('data-path-pdf-trip', $path_pdf_trip);
                            $button->attributes->set('data-path-pdf-salling-request', $path_pdf_salling_request);
                            $button->attributes->set('data-route', $route->id);
                            $button->attributes->set('data-transport', $transport->id);
                            $button->attributes->set('data-date-trip', $date);
                            $button->attributes->set('data-date-trip-display', git_date_format($date));
                            $button->render();
                            ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
        <?php
    }

    private function modal_table_content(Route $route, Transport $transport)
    {
        $path_pdf_trip = add_query_arg(
            ['action' => 'git_pdf_trip'],
            admin_url('admin-ajax.php')
        );
        $path_pdf_salling_request = add_query_arg(
            ['action' => 'git_pdf_salling_request'],
            admin_url('admin-ajax.php')
        );
        $path_finish_trip = add_query_arg(
            ['action' => 'git_finish_trip'],
            admin_url('admin-ajax.php')
        );
        ob_start();
        ?>
        <form id="git_trip_form">
            <?php wp_nonce_field(self::NONCE_ACTION) ?>
            <input type="hidden" name="date_trip">
            <input type="hidden" name="id_route" value="<?= $route->id ?>">
            <input type="hidden" name="id_transport" value="<?= $transport->id ?>">
            <table class="table table-bordered table-striped table-hover">
                <tr>
                    <th>Origen</th>
                    <td><?= $route->getOrigin()->name ?></td>
                </tr>
                <tr>
                    <th>Destino</th>
                    <td><?= $route->getDestiny()->name ?></td>
                </tr>
                <tr>
                    <th>Horario</th>
                    <td><?= $route->getDepartureTime()->pretty() ?></td>
                </tr>
                <tr>
                    <th>Viaje</th>
                    <td id="cell-date-trip"></td>
                </tr>
                <tr>
                    <th>Transporte</th>
                    <td><?= $transport->nicename ?></td>
                </tr>
                <tr>
                    <th>Pasajeros</th>
                    <td id="cell-passengers-count"></td>
                </tr>
            </table>
            <div class="btn-group">
                <button class="btn btn-warning" formaction="<?= $path_finish_trip ?>" formmethod="post">Finalizar
                    Trayecto</button>
                <button class="btn btn-primary" formaction="<?= $path_pdf_trip ?>" formmethod="post">Lista de embarque</button>
                <button class="btn btn-success" formaction="<?= $path_pdf_salling_request ?>" formmethod="post">Solicitud de
                    Zarpe</button>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }

    private function get_passengers(int $transport, int $route, string $date)
    {
        if ($transport <= 0 || $route <= 0) {
            return [];
        }

        $repository = new PassengerService();

        return $repository->find([
            'id_transport' => $transport,
            'id_route' => $route,
            'date_trip' => $date,
            'approved' => true,
            'served' => false,
        ])->getItems();
    }

    private function get_transport(int $transport)
    {
        if ($transport < 0) {
            return null;
        }
        $repository = new TransportService();
        $result = $repository->find(['id' => $transport]);
        if ($result->hasItems()) {
            return $result->getItems()[0];
        }
        return null;
    }

    private function get_route(int $origin, int $destiny, string $schedule)
    {
        if (empty($schedule) || $origin < 0 || $destiny < 0) {
            return null;
        }
        $repository = new RouteService();
        $result = $repository->find([
            'id_origin' => $origin,
            'id_destiny' => $destiny,
            'departure_time' => $schedule,
        ]);
        if ($result->hasItems()) {
            return $result->getItems()[0];
        }
        return null;
    }

    private function obtenerFechasEntre(string $inicio, string $fin)
    {
        try {
            $fechaInicio = new DateTime($inicio);
            $fechaFin = new DateTime($fin);
        } catch (Exception $e) {
            return null;
        }

        $fechaFinInclusiva = clone $fechaFin;
        $fechaFinInclusiva->modify('+1 day');

        $intervalo = new DateInterval('P1D');
        $rango = new DatePeriod($fechaInicio, $intervalo, $fechaFinInclusiva);

        $fechas = [];
        foreach ($rango as $fecha) {
            $fechas[] = $fecha->format('Y-m-d');
        }

        return $fechas;
    }

    private function showMessage()
    {
        MessageAlert::getInstance(self::class)->render();
    }

    public static function writeMessage(string $message, MessageLevel $level = MessageLevel::INFO, int $expiration_seconds = 30)
    {
        (new MessageTemporal)->writeTemporalMessage(
            $message,
            self::class,
            $level,
            $expiration_seconds
        );
    }
}
