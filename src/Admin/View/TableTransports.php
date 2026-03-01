<?php
namespace CentralBooking\Admin\View;

use CentralBooking\Admin\AdminRouter;
use CentralBooking\Admin\Form\FormTransport;
use CentralBooking\Data\Repository\ResultSetInterface;
use CentralBooking\Data\Transport;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\GUI\PaginationComponent;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

final class TableTransports implements DisplayerInterface
{
    /**
     * @var ResultSetInterface<Transport>
     */
    private ResultSetInterface $resultSet;
    public const NONCE_ACTION = 'transport_table_nonce_action';

    public function __construct()
    {
        $this->resultSet = $this->fetchTransports();
    }

    private function fetchTransports(): ResultSetInterface
    {
        $pageSize = isset($_GET['page_size']) ? (int) $_GET['page_size'] : 10;
        $pageNumber = isset($_GET['page_number']) ? (int) $_GET['page_number'] : 1;
        $filter = [];

        foreach ($_GET as $key => $value) {
            $filter[$key] = $value;
        }

        $args = array_merge($filter, [
            'order_by' => $this->getCurrentOrderBy(),
            'order' => $this->getCurrentOrder(),
            'limit' => $pageSize,
            'offset' => ($pageNumber - 1) * $pageSize,
        ]);

        return git_transports_result_set($args);
    }

    private function getCurrentOrderBy()
    {
        $order_by = $_GET['order_by'] ?? 'id';
        return in_array(
            $order_by,
            ['nicename', 'code', 'type', 'id_operator']
        ) ? $order_by : 'id';
    }

    private function getCurrentOrder()
    {
        $order = $_GET['order'] ?? 'DESC';
        return $order === 'DESC' ? 'DESC' : 'ASC';
    }

    private function createOrderLink(string $order_by, string $order)
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
            link_next: add_query_arg(['page_number' => ($this->resultSet->getCurrentPage() + 1)]),
            link_prev: add_query_arg(['page_number' => ($this->resultSet->getCurrentPage() - 1)])
        );
        $this->readMessage();
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <?php $this->headerOrder('Nombre', 'nicename') ?>
                    <?php $this->headerOrder('Código', 'code') ?>
                    <th scope="col">Capacidad</th>
                    <?php $this->headerOrder('Tipo', 'type') ?>
                    <th scope="col">Disponibilidad</th>
                    <?php $this->headerOrder('Operador', 'id_operator') ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->resultSet->getItems() as $transport): ?>
                    <tr>
                        <td style="padding-bottom: 0;">
                            <a href="<?= esc_url(AdminRouter::get_url_for_class(
                                FormTransport::class,
                                ['id' => $transport->id]
                            )) ?>">
                                <strong>
                                    <?= esc_html($transport->nicename) ?>
                                </strong>
                            </a>
                        </td>
                        <td style="padding-bottom: 0;"><?= esc_html($transport->code) ?></td>
                        <td style="padding-bottom: 0;"><?= esc_html($transport->getCapacity()) ?></td>
                        <td style="padding-bottom: 0;"><?= esc_html($transport->type->label()) ?></td>
                        <td style="padding-bottom: 0;"><?= $transport->isAvailable() ? 'Disponible' : 'No disponible' ?></td>
                        <td style="padding-bottom: 0;">
                            <?= esc_html($transport->getOperator()->getUser()->first_name . ' ' . $transport->getOperator()->getUser()->last_name) ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6" style="padding-top: 0;">
                            <?php $this->actionPanel($transport); ?>
                        </td>
                    </tr>
                    <tr id="actions-container-<?= $transport->id ?>" class="git-row-actions">
                        <td colspan="6">
                            <?php $this->actionContainer($transport); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        $pagination->render();
    }

    private function actionContainer(Transport $transport)
    {
        ?>

        <!-- Rutas del transporte -->

        <div id="routes-container-<?= $transport->id ?>" class="git-item-container hidden"
            data-parent="#actions-container-<?= $transport->id ?>">
            <?php foreach ($transport->getRoutes() as $route): ?>
                <div class="git-item">
                    <table style="border-spacing: 20px 3px; border-collapse: separate;">
                        <tr>
                            <td><b>Origen:</b></td>
                            <td><?= $route->getOrigin()->name ?></td>
                        </tr>
                        <tr>
                            <td><b>Destino:</b></td>
                            <td><?= $route->getDestiny()->name ?></td>
                        </tr>
                        <tr>
                            <td><b>Hora:</b></td>
                            <td><?= $route->getDepartureTime()->pretty() ?></td>
                        </tr>
                    </table>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Servicios del transporte -->

        <div id="services-container-<?= $transport->id ?>" class="git-item-container hidden"
            data-parent="#actions-container-<?= $transport->id ?>">
            <?php foreach ($transport->getServices() as $service): ?>
                <div class="git-item">
                    <?= esc_html($service->name) ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Fechas de mantenimiento del transporte -->

        <div id="availability-container-<?= $transport->id ?>" class="git-item-container hidden"
            data-parent="#actions-container-<?= $transport->id ?>">
            <?php $this->formTransportAvailability($transport) ?>
        </div>
        <?php
    }

    private function actionPanel(Transport $transport)
    {
        ?>
        <div class="row-actions visible">
            <span class="edit">
                <a class="git-row-action-link" href="#routes-container-<?= $transport->id ?>">Rutas
                    (<?= count($transport->getRoutes()) ?>)</a>
            </span>
            <span> | </span>
            <span class="edit">
                <a class="git-row-action-link" href="#services-container-<?= $transport->id ?>">Servicios
                    (<?= count($transport->getServices()) ?>)</a>
            </span>
            <span> | </span>
            <span class="edit">
                <a class="git-row-action-link" href="#availability-container-<?= $transport->id ?>">Disponibilidad</a>
            </span>
        </div>
        <?php
    }

    private function formTransportAvailability(Transport $transport)
    {
        $date_end_input = new InputComponent('date_end', 'date');
        $date_start_input = new InputComponent('date_start', 'date');
        $date_end_input->setValue($transport->getMaintenanceDates()['date_end']);
        $date_start_input->setValue($transport->getMaintenanceDates()['date_start']);
        $date_end_input->setRequired(true);
        $date_start_input->setRequired(true);

        $action_url = add_query_arg(
            ['action' => 'git_transport_maintenance'],
            admin_url('admin-ajax.php')
        );

        ?>
        <div style="border-left: 5px solid gray; max-width: 500px; padding: 0 0 0 20px; margin: 10px 0 10px 10px;">
            <form class="form-availability" method="post" action="<?= $action_url ?>">
                <?php git_nonce_field(); ?>
                <input type="hidden" name="id" value="<?= $transport->id ?>">
                <table class="form-table" role="presentation">
                    <tr>
                        <td scope="row">
                            <?php $date_start_input->getLabel('Inicio')->render() ?>
                        </td>
                        <td>
                            <?= $date_start_input->render() ?>
                        </td>
                    </tr>
                    <tr>
                        <td scope="row">
                            <?php $date_end_input->getLabel('Fin')->render() ?>
                        </td>
                        <td>
                            <?= $date_end_input->render() ?>
                        </td>
                    </tr>
                </table>
                <button class="button button-primary" type="submit">Establecer</button>
            </form>
        </div>
        <?php
    }

    private function headerOrder(string $label, string $order_by)
    {
        $class = "manage-column " . ($this->getCurrentOrderBy() === $order_by ? 'sorted' : 'sortable') . " " . ($this->getCurrentOrder() === 'ASC' ? 'asc' : 'desc');
        ?>
        <th scope="col" class="<?= $class ?>">
            <a href="<?= $this->createOrderLink($order_by, $this->getCurrentOrder() === 'ASC' ? 'DESC' : 'ASC') ?>">
                <span><?= $label ?></span>
                <span class="sorting-indicators">
                    <span class="sorting-indicator asc"></span>
                    <span class="sorting-indicator desc"></span>
                </span>
            </a>
        </th>
        <?php
    }

    private function readMessage()
    {
        MessageAlert::getInstance(TableTransports::class)->render();
    }

    public static function writeMessage(string $message, MessageLevel $level = MessageLevel::SUCCESS, int $expiration_seconds = 30)
    {
        (new MessageTemporal)->writeTemporalMessage(
            $message,
            TableTransports::class,
            $level,
            $expiration_seconds
        );
    }
}
