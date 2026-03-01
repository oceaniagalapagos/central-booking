<?php
namespace CentralBooking\Admin\View;

use CentralBooking\Admin\AdminRouter;
use CentralBooking\Admin\Form\FormLocation;
use CentralBooking\Admin\Form\FormZone;
use CentralBooking\Data\Location;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

final class TableLocations implements DisplayerInterface
{
    /**
     * @var array<Location>
     */
    private array $locations;

    public function __construct()
    {
        $this->locations = $this->fetchLocations();
    }

    private function fetchLocations()
    {
        return git_locations([
            'order_by' => $_GET['order_by'] ?? 'id',
            'order' => $_GET['order'] ?? 'DESC',
        ]);
    }

    private function get_current_order_by()
    {
        $order_by = $_GET['order_by'] ?? 'id';
        return in_array(
            $order_by,
            ['name', 'name_zone']
        ) ? $order_by : 'id';
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
        $this->showMessage();
        ?>
        <div style="max-width: 500px;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col"
                            class="manage-column <?= $this->get_current_order_by() === 'name' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('name', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Ubicación</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                        <th scope="col"
                            class="manage-column <?= $this->get_current_order_by() === 'name_zone' ? 'sorted' : 'sortable' ?> <?= $this->get_current_order() === 'ASC' ? 'asc' : 'desc' ?>">
                            <a
                                href="<?= $this->create_order_link('name_zone', $this->get_current_order() === 'ASC' ? 'DESC' : 'ASC') ?>">
                                <span>Zona</span>
                                <span class="sorting-indicators">
                                    <span class="sorting-indicator asc"></span>
                                    <span class="sorting-indicator desc"></span>
                                </span>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->locations as $location): ?>
                        <tr>
                            <td>
                                <a href="<?= AdminRouter::get_url_for_class(FormLocation::class, ['id' => $location->id]) ?>">
                                    <strong>
                                        <?= esc_html($location->name) ?>
                                    </strong>
                                </a>
                            </td>
                            <td>
                                <a href="<?= AdminRouter::get_url_for_class(FormZone::class, ['id' => $location->getZone()->id]) ?>">
                                    <strong>
                                        <?= esc_html($location->getZone()->name) ?>
                                    </strong>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    private function showMessage()
    {
        MessageAlert::getInstance(self::class)->render();
    }

    public static function writeMessage(string $message, MessageLevel $level = MessageLevel::INFO, int $expiration_seconds = 30)
    {
        (new MessageTemporal())->writeTemporalMessage(
            $message,
            self::class,
            $level,
            $expiration_seconds
        );
    }
}