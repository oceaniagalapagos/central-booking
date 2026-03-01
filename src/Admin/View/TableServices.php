<?php
namespace CentralBooking\Admin\View;

use CentralBooking\Admin\AdminRouter;
use CentralBooking\Admin\Form\FormService;
use CentralBooking\Data\Repository\ResultSetInterface;
use CentralBooking\Data\Service;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

final class TableServices implements DisplayerInterface
{
    /**
     * Summary of result_set
     * @var ResultSetInterface<Service>
     */
    private ResultSetInterface $resultSet;

    public function __construct()
    {
        $this->resultSet = $this->getResultSet();
    }

    private function getResultSet(): ResultSetInterface
    {
        $limit = -1;
        $offset = 0;
        // $limit = (int) ($_GET['page_size'] ?? 10);
        // $offset = (int) ($_GET['page_number'] ?? 1);
        return git_services_result_set([
            'order' => $this->getCurrentOrder(),
            'order_by' => $this->getCurrentOrderBy(),
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    private function getCurrentOrderBy()
    {
        $order_by = $_GET['order_by'] ?? 'id';
        return in_array(
            $order_by,
            ['name', 'price']
        ) ? $order_by : 'id';
    }

    private function getCurrentOrder()
    {
        $order = $_GET['order'] ?? 'ASC';
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
        $this->showMessage();
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <?php $this->headerOrder('Nombre', 'name'); ?>
                    <?php $this->headerOrder('Precio', 'price'); ?>
                    <th scope="col">Ícono</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->resultSet->getItems() as $service): ?>
                    <tr>
                        <td style="padding-bottom: 0;">
                            <a href="<?= esc_url(AdminRouter::get_url_for_class(
                                FormService::class,
                                ['id' => $service->id]
                            )) ?>">
                                <strong>
                                    <?= esc_html($service->name) ?>
                                </strong>
                            </a>
                        </td>
                        <td style="padding-bottom: 0;">
                            <?= git_currency_format(esc_html($service->price), true) ?>
                        </td>
                        <td style="padding-bottom: 0;">
                            <img src="<?= esc_url($service->icon) ?>" alt="<?= esc_html($service->name) ?>" width="24px">
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" style="padding-top: 0;">
                            <?php $this->actionPanel($service); ?>
                        </td>
                    </tr>
                    <tr id="actions-container-<?= $service->id ?>" class="git-row-actions">
                        <td colspan="3">
                            <?php $this->actionContainer($service); ?>
                        </td>
                    </tr>
                    <tr id="actions-container-<?= $service->id ?>" class="git-row-actions">
                        <td colspan="3">
                            <?php $this->actionContainer($service); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    private function actionContainer(Service $service)
    {
        ?>
        <div id="transport-container-<?= $service->id ?>" class="git-item-container hidden"
            data-parent="#actions-container-<?= $service->id ?>">
            <?php foreach ($service->getTransports() as $transport): ?>
                <div class="git-item">
                    <?= esc_html($transport->nicename) ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    private function actionPanel(Service $service)
    {
        ?>
        <div class="row-actions visible">
            <span class="edit">
                <a class="git-row-action-link" href="#transport-container-<?= $service->id ?>">Transportes
                    (<?= count($service->getTransports()) ?>)</a>
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
        MessageAlert::getInstance(TableServices::class)->render();
    }

    public static function writeMessage(string $message, MessageLevel $level = MessageLevel::INFO, int $expiration_seconds = 30)
    {
        (new MessageTemporal)->writeTemporalMessage(
            $message,
            TableServices::class,
            $level,
            $expiration_seconds
        );
    }
}