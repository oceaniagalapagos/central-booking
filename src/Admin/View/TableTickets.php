<?php
namespace CentralBooking\Admin\View;

use CentralBooking\Admin\AdminRouter;
use CentralBooking\Admin\Form\FormTicket;
use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\Data\Repository\ResultSetInterface;
use CentralBooking\Data\Ticket;
use CentralBooking\GUI\AccordionComponent;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\GUI\PaginationComponent;
use CentralBooking\GUI\SelectComponent;
use CentralBooking\GUI\TextComponent;
use CentralBooking\Implementation\GUI\CouponSelect;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

final class TableTickets implements DisplayerInterface
{
    /**
     * @var ResultSetInterface<Ticket>
     */
    private ResultSetInterface $resultSet;
    public const NONCE_ACTION = 'git_toggle_flexible_ticket';

    public function __construct()
    {
        $this->resultSet = $this->fetchTransports();
    }

    private function fetchTransports()
    {
        $pageNumber = isset($_GET['page_number']) ? (int) $_GET['page_number'] : 1;
        $pageSize = isset($_GET['page_size']) ? (int) $_GET['page_size'] : 10;
        $filter = ['status_not' => TicketStatus::PERORDER];

        foreach ($_GET as $key => $value) {
            $filter[$key] = $value;
        }

        $filter['order_by'] = $_GET['order_by'] ?? 'date_creation';
        $filter['order'] = $_GET['order'] ?? 'DESC';
        $filter['limit'] = $pageSize;
        $filter['offset'] = ($pageNumber - 1) * $pageSize;

        return git_tickets_result_set($filter);
    }

    private function filterForm()
    {
        $ticket_status_select = git_ticket_status_select_field('status');
        $coupon_select = (new CouponSelect('id_coupon'))->create();
        $flexible_select = new SelectComponent('flexible');
        $date_creation_input = new InputComponent('date_creation', 'date');
        $flexible_select->addOption('Seleccione...', '');
        $flexible_select->addOption('Sí', 'true');
        $flexible_select->addOption('No', 'false');
        $flexible_select->setValue($_GET['flexible'] ?? '');
        $date_creation_input->setValue($_GET['date_creation'] ?? '');
        $ticket_status_select->setValue($_GET['status'] ?? '');
        $coupon_select->setValue($_GET['id_coupon'] ?? '');
        ob_start();
        ?>
        <form method="GET">
            <input type="hidden" name="page" value="<?= esc_attr($_GET['page'] ?? 'git_tickets') ?>">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php $ticket_status_select->getLabel('Estado')->render(); ?></th>
                    <td><?php $ticket_status_select->render(); ?></td>
                    <th scope="row"><?php $date_creation_input->getLabel('Fecha de Compra')->render(); ?></th>
                    <td><?php $date_creation_input->render(); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php $coupon_select->getLabel('Cupon')->render(); ?></th>
                    <td><?php $coupon_select->render(); ?></td>
                    <th scope="row"><?php $flexible_select->getLabel('Flexible')->render(); ?></th>
                    <td><?php $flexible_select->render(); ?></td>
                </tr>
            </table>
            <button class="button button-primary" type="submit">Aplicar</button>
            <button class="button" type="reset">Limpiar</button>
        </form>
        <?php
        $result = ob_get_clean();
        return git_string_to_component($result);
    }

    public function render()
    {
        $this->showMessage();
        $pagination = new PaginationComponent();
        $pagination->setData(
            $this->resultSet->getTotalItems(),
            $this->resultSet->getCurrentPage(),
            $this->resultSet->getTotalPages()
        );
        $pagination->setLinks(
            add_query_arg(['page_number' => 1]),
            add_query_arg(['page_number' => $this->resultSet->getTotalPages()]),
            add_query_arg(['page_number' => ($this->resultSet->getCurrentPage() + 1)]),
            add_query_arg(['page_number' => ($this->resultSet->getCurrentPage() - 1)]),
        );

        $accordion = new AccordionComponent();
        $filter_header = new TextComponent('span');
        $filter_header->append(git_string_to_component('<i class="bi bi-sliders"></i>'));
        $filter_header->append(' Filtro');
        $accordion->addItem($filter_header, $this->filterForm());
        $accordion->render();
        ?>
        <div style="overflow-x: auto; max-width: 1100px;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <?php $this->headerOrder('Fecha de Compra', 'date_creation') ?>
                        <?php $this->headerOrder('Precio', 'total_amount') ?>
                        <?php $this->headerOrder('Estado', 'status') ?>
                        <?php $this->headerOrder('Flexible', 'flexible') ?>
                        <?php $this->headerOrder('Cupón', 'code_coupon') ?>
                        <th scope="col">Teléfono</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->resultSet->getItems() as $ticket): ?>
                        <tr>
                            <td style="padding-bottom: 0;">
                                <a href="<?= esc_url(AdminRouter::get_url_for_class(
                                    FormTicket::class,
                                    ['id' => $ticket->id]
                                )) ?>">
                                    <strong>
                                        <?= git_datetime_format($ticket->getOrder()->get_date_created()->format('Y-m-d H:i:s')) ?>
                                    </strong>
                                </a>
                            </td>
                            <td style="padding-bottom: 0;"><?= git_currency_format($ticket->total_amount, true) ?></td>
                            <td style="padding-bottom: 0;"><?= $ticket->status->label() ?></td>
                            <td style="padding-bottom: 0;"><?= $ticket->flexible ? 'Sí' : 'No' ?></td>
                            <td style="padding-bottom: 0;">
                                <?= $ticket->getCoupon() === null ? '—' : $ticket->getCoupon()->post_title ?>
                            </td>
                            <td style="padding-bottom: 0;"><?= $ticket->getOrder()?->get_billing_phone() ?? '' ?></td>
                        </tr>
                        <tr>
                            <td colspan="6" style="padding-top: 0;">
                                <?php $this->actionPanel($ticket); ?>
                            </td>
                        </tr>
                        <tr id="actions-container-<?= $ticket->id ?>" class="git-row-actions">
                            <td colspan="6">
                                <?php $this->actionContainer($ticket); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php $pagination->render() ?>
        </div>
        <?php
    }

    private function actionContainer(Ticket $ticket)
    {
        ?>
        <div id="edit-container-<?= $ticket->id ?>" class="git-item-container hidden"
            data-parent="#actions-container-<?= $ticket->id ?>">
            <?php $this->formTicketFlexible($ticket); ?>
        </div>
        <?php
    }

    private function formTicketFlexible(Ticket $ticket)
    {
        $input_check = new InputComponent('flexible', 'checkbox');
        $input_check->styles->set('margin', '0 0 0 10px');

        if ($ticket->flexible) {
            $input_check->attributes->set('checked', '');
        }

        $action_url = add_query_arg(
            ['action' => 'git_edit_toggle_flexible'],
            admin_url('admin-ajax.php')
        );

        ?>
        <div style="border-left: 5px solid gray; max-width: 500px; padding: 0 0 0 20px; margin: 10px 0 10px 10px;">
            <form class="toggle-ticket-flexible" action="<?= esc_url($action_url) ?>" method="post">
                <?php wp_nonce_field(self::NONCE_ACTION); ?>
                <input type="hidden" name="id" value="<?= esc_attr($ticket->id) ?>">
                <p>
                    <?php $input_check->getLabel('Flexible')->render() ?>
                    <?php $input_check->render() ?>
                </p>
                <button class="button button-primary" type="submit">Enviar</button>
            </form>
        </div>
        <?php
    }

    private function actionPanel(Ticket $ticket)
    {
        ?>
        <div class="row-actions visible">
            <span>
                <a target="_blank" href="<?= admin_url("post.php?post={$ticket->getOrder()->get_id()}&action=edit") ?>">Pedido:
                    <?= esc_html($ticket->getOrder()->get_id()) ?></a>
            </span>
            <span> | </span>
            <span>
                <a target="_blank"
                    href="<?= AdminRouter::get_url_for_class(TablePassengers::class, ['id_ticket' => $ticket->id]) ?>">
                    Pasajeros (<?= count($ticket->getPassengers()) ?>)
                </a>
            </span>
            <span> | </span>
            <span>
                <a class="git-row-action-link" target="_blank"
                    href="<?= AdminRouter::get_url_for_class(TableTicketsLog::class, ['id' => $ticket->id]) ?>">
                    Logs
                </a>
            </span>
            <span> | </span>
            <span>
                <a class="git-row-action-link" href="#edit-container-<?= $ticket->id ?>">
                    Ticket Flexible
                </a>
            </span>
        </div>
        <?php
    }

    private function getCurrentOrderBy()
    {
        $order_by = $_GET['order_by'] ?? 'id';
        return in_array(
            $order_by,
            ['date_creation', 'total_amount', 'status', 'id_coupon', 'flexible']
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
        MessageAlert::getInstance(TableTickets::class)->render();
    }

    public static function writeMessage(string $message, MessageLevel $level = MessageLevel::INFO, int $expiration_seconds = 30)
    {
        (new MessageTemporal())->writeTemporalMessage(
            $message,
            TableTickets::class,
            $level,
            $expiration_seconds
        );
    }
}
