<?php
namespace CentralBooking\Data\Services;

use CentralBooking\Data\Constants\LogLevel;
use CentralBooking\Data\Constants\LogSource;
use CentralBooking\Data\ORM\ORMInterface;
use CentralBooking\Data\ORM\PassengerORM;
use CentralBooking\Data\Passenger;
use CentralBooking\Data\Repository\PassengerRepository;
use Exception;

class PassengerService
{
    private PassengerRepository $repository;
    private ORMInterface $orm;
    public ErrorService $lastError = ErrorService::NO_ERROR;
    private static ?PassengerService $instance = null;

    public static function getInstance(): PassengerService
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $wpdb = $GLOBALS['wpdb'];
        if ($wpdb) {
            $this->repository = new PassengerRepository($wpdb);
            $this->orm = new PassengerORM();
        } else {
            throw new Exception('Error en la variable gloabl wpdb');
        }
    }

    private function notify_transfer(Passenger $original_passenger, Passenger $new_passenger)
    {
        ob_start();
        ?>
        <table>
            <tbody>
                <tr>
                    <th colspan="2"><strong>Cambio de Ruta</strong></th>
                </tr>
                <tr>
                    <th scope="col"><strong>Pasajero:</strong></th>
                    <td><?= $new_passenger->name ?></td>
                </tr>
                <tr>
                    <th scope="col"><strong>Viaje anterior:</strong></th>
                    <td>
                        <table>
                            <tbody>
                                <tr>
                                    <th>
                                        <strong>Origen:</strong>
                                    </th>
                                    <td>
                                        <s><?= $original_passenger->getRoute()->getOrigin()->name ?></s>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <strong>Destino:</strong>
                                    </th>
                                    <td>
                                        <s><?= $original_passenger->getRoute()->getDestiny()->name ?></s>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <strong>Fecha:</strong>
                                    </th>
                                    <td>
                                        <s><?= git_date_format($original_passenger->getDateTrip()->format()) ?></s>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <strong>Transporte:</strong>
                                    </th>
                                    <td>
                                        <s><?= $original_passenger->getTransport()->nicename ?></s>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <th scope="col"><strong>Viaje nuevo:</strong></th>
                    <td>
                        <table>
                            <tbody>
                                <tr>
                                    <th>
                                        <strong>Origen:</strong>
                                    </th>
                                    <td>
                                        <?= $new_passenger->getRoute()->getOrigin()->name ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <strong>Destino:</strong>
                                    </th>
                                    <td>
                                        <?= $new_passenger->getRoute()->getDestiny()->name ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <strong>Fecha:</strong>
                                    </th>
                                    <td>
                                        <?= git_date_format($new_passenger->getDateTrip()->format()) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <strong>Transporte:</strong>
                                    </th>
                                    <td>
                                        <?= $new_passenger->getTransport()->nicename ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <th scope="col"><strong>Responsable:</strong></th>
                    <td>
                        <code><?= wp_get_current_user()->user_login ?></code>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
        git_log_create(
            source: LogSource::PASSENGER,
            id_source: $new_passenger->id,
            message: ob_get_clean(),
            level: LogLevel::INFO,
        );
    }

    public function save(Passenger $passenger)
    {
        return $this->repository->save($passenger);
    }

    public function find(
        array $args = [],
        string $order_by = 'id',
        string $order = 'ASC',
        int $limit = -1,
        int $offset = 0
    ) {
        return $this->repository->find(
            $this->orm,
            $args,
            $order_by,
            $order,
            $limit,
            $offset
        );
    }
}
