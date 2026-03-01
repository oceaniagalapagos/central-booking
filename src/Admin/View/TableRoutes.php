<?php
namespace CentralBooking\Admin\View;

use CentralBooking\Admin\Form\FormRoute;
use CentralBooking\Data\Repository\ResultSetInterface;
use CentralBooking\Data\Route;
use CentralBooking\Admin\AdminRouter;
use CentralBooking\GUI\Constants\AlignmentConstants;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\PaginationComponent;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

final class TableRoutes implements DisplayerInterface
{
    /**
     * @var ResultSetInterface<Route>
     */
    private ResultSetInterface $resultSet;

    public function __construct()
    {
        $this->resultSet = $this->fetchServices();
    }

    private function fetchServices()
    {
        $page_size = isset($_GET['page_size']) ? (int) $_GET['page_size'] : 10;
        $page_number = isset($_GET['page_number']) ? (int) $_GET['page_number'] : 1;
        $result = git_routes_result_set([
            'order_by' => $this->getCurrentOrderBy(),
            'order' => $this->getCurrentOrder(),
            'limit' => $page_size,
            'offset' => ($page_number - 1) * $page_size,
        ]);
        $this->total_items = $result->getTotalItems();
        $this->total_pages = $result->getTotalPages();
        $this->current_page = $result->getCurrentPage();
        return $result;
    }

    private function getCurrentOrderBy()
    {
        $order_by = $_GET['order_by'] ?? 'id';
        return in_array(
            $order_by,
            ['name_origin', 'name_destiny', 'type', 'duration_trip', 'departure_time']
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
        $pagination = new PaginationComponent(false, AlignmentConstants::RIGHT);
        $pagination->setData(
            total_items: $this->resultSet->getTotalItems(),
            current_page: $this->resultSet->getCurrentPage(),
            total_pages: $this->resultSet->getTotalPages(),
        );
        $pagination->setLinks(
            link_first: add_query_arg(['page_number' => 1]),
            link_last: add_query_arg(['page_number' => $this->resultSet->getTotalPages()]),
            link_next: add_query_arg(['page_number' => ($this->resultSet->getCurrentPage() + 1)]),
            link_prev: add_query_arg(['page_number' => ($this->resultSet->getCurrentPage() - 1)])
        );
        $this->showMessage();
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <?php $this->headerOrder('Origen', 'origin_name') ?>
                    <?php $this->headerOrder('Destino', 'destiny_name') ?>
                    <?php $this->headerOrder('Hora de salida', 'departure_time') ?>
                    <?php $this->headerOrder('Hora de llegada', 'arrival_time') ?>
                    <?php $this->headerOrder('Tipo', 'type') ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->resultSet->getItems() as $route): ?>
                    <tr>
                        <td style="padding-bottom: 0;">
                            <a href="<?= AdminRouter::get_url_for_class(FormRoute::class, ['id' => $route->id]) ?>">
                                <strong>
                                    <!-- <i class="bi bi-geo"></i> -->
                                    <?= esc_html($route->getOrigin()->name) ?>
                                </strong>
                            </a>
                        </td>
                        <td style="padding-bottom: 0;">
                            <a href="<?= AdminRouter::get_url_for_class(FormRoute::class, ['id' => $route->id]) ?>">
                                <strong>
                                    <!-- <i class="bi bi-arrow-right"></i> -->
                                    <?= esc_html($route->getDestiny()->name) ?>
                                </strong>
                            </a>
                        </td>
                        <td style="padding-bottom: 0;"><?= $route->getDepartureTime()->pretty() ?></td>
                        <td style="padding-bottom: 0;"><?= $route->getArrivalTime()->pretty() ?></td>
                        <td style="padding-bottom: 0;"><?= $route->type->label() ?></td>
                    </tr>
                    <tr>
                        <td colspan="5" style="padding-top: 0;">
                            <?php $this->actionPanel($route); ?>
                        </td>
                    </tr>
                    <tr id="actions-container-<?= $route->id ?>" class="git-row-actions">
                        <td colspan="5">
                            <?php $this->actionContainer($route); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        $pagination->render();
    }

    private function actionContainer(Route $route)
    {
        ?>
        <!-- Transportes de ruta -->
        <div id="transport-container-<?= $route->id ?>" class="git-item-container hidden"
            data-parent="#actions-container-<?= $route->id ?>">
            <?php foreach ($route->getTransports() as $transport): ?>
                <div class="git-item">
                    <?= esc_html($transport->nicename) ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    private function actionPanel(Route $route)
    {
        ?>
        <div class="row-actions visible">
            <span class="edit">
                <a href="#transport-container-<?= $route->id ?>" class="git-row-action-link"
                    data-route="<?= esc_attr($route->id) ?>">
                    Transportes (<?= count($route->getTransports()) ?>)
                </a>
            </span>
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