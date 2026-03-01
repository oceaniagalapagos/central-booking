<?php
namespace CentralBooking\Profile\Tables;

use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\GUI\ComponentBuilder;
use CentralBooking\GUI\Constants\ButtonStyleConstants;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\ModalComponent;
use CentralBooking\Utils\Actions\DownloadInvoiceInfo;
use CentralBooking\Utils\Actions\InvoiceInfoPagination;

final class TableInvoice implements DisplayerInterface
{
    private ModalComponent $download_modal;
    private InvoiceInfoPagination $invoice_pagination;

    public function __construct()
    {
        $this->download_modal = new ModalComponent('Descargar información de facturación');
        $this->init_modal_download();
    }

    private function get_operator()
    {
        $operator_id = $_GET['operator'] ?? 0;
        if (!is_numeric($operator_id)) {
            return null;
        }
        return git_operator_by_id((int) $operator_id);
    }

    public function render()
    {
        $this->download_modal->render();
        ?>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Nro. Ticket</th>
                    <th>Fecha de Compra</th>
                    <th>Pedido</th>
                    <th>Cliente</th>
                    <th>Precio</th>
                    <th>Cupon</th>
                    <th>Abono</th>
                    <th>Estado</th>
                    <th>Saldo</th>
                </tr>
            </thead>
            <tbody>
                <?php $tickets = $this->fetch_tickets();
                foreach ($tickets as $ticket):
                    $proof_payment = $ticket->getProofPayment();
                    $abono = 0;
                    if ($ticket->getCoupon() !== null) {
                        if ($ticket->status === TicketStatus::PAYMENT) {
                            $abono = $ticket->total_amount;
                        } elseif ($ticket->status === TicketStatus::PARTIAL) {
                            $abono = $proof_payment?->amount ?? 0;
                        } elseif ($ticket->status === TicketStatus::CANCEL) {
                            $abono = 0;
                        } else {
                            $abono = $proof_payment?->amount ?? 0;
                        }
                    } else {
                        $abono = $ticket->total_amount;
                    }
                    $saldo = $ticket->total_amount - $abono;
                    ?>
                    <tr class="<?= $saldo !== 0 ? 'table-danger' : '' ?>">
                        <td><?= $ticket->id; ?></td>
                        <td>
                            <time datetime="<?= $ticket->getOrder()->get_date_created()->format('Y-m-d H:i:s'); ?>">
                                <?= git_datetime_format($ticket->getOrder()->get_date_created()->format('Y-m-d H:i:s')); ?>
                            </time>
                        </td>
                        <td><?= $ticket->getOrder()->get_id(); ?></td>
                        <td><?= $ticket->getOrder()->get_billing_first_name(); ?></td>
                        <td><?= git_currency_format($ticket->total_amount, true); ?></td>
                        <td><?= $ticket->getCoupon() ? $ticket->getCoupon()->post_title : '—'; ?></td>
                        <td><?= git_currency_format($abono, true); ?></td>
                        <td><?= $ticket->status->label() ?></td>
                        <td><?= git_currency_format($saldo, true); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        if (!empty($tickets)) {
            $this->render_pagination();
        }
    }

    private function get_limits_from_month()
    {
        return [
            'start' => $_GET['date_start'] ?? date('Y-m-01'),
            'end' => $_GET['date_end'] ?? date('Y-m-t'),
        ];
    }

    public function fetch_tickets()
    {
        $limits = $this->get_limits_from_month();

        $coupon = null;

        if (isset($_GET['coupon']) && is_numeric($_GET['coupon']) && $_GET['coupon'] > 0) {
            $coupon = get_post($_GET['coupon']);
            if (!$coupon || $coupon->post_type !== 'shop_coupon') {
                $coupon = null;
            }
        }

        $operator = $this->get_operator();

        if ($operator === null) {
            return [];
        }

        $this->invoice_pagination = new InvoiceInfoPagination(
            $operator,
            $limits['start'],
            $limits['end'],
            $coupon
        );

        $this->invoice_pagination->current_page = isset($_GET['page_number']) && is_numeric($_GET['page_number']) && $_GET['page_number'] > 0 ? (int) $_GET['page_number'] : 1;

        return $this->invoice_pagination->fetch_tickets();
    }

    private function init_modal_download()
    {
        $action = add_query_arg(
            ['action' => 'download_invoice_csv'],
            admin_url('admin-ajax.php')
        );
        ob_start();
        ?>
        <form method="POST" action="<?= esc_url($action) ?>">
            <h3>Seleccionar columnas para descargar</h3>
            <?= git_nonce_field() ?>
            <input type="hidden" name="operator" value="<?= esc_attr($_GET['operator'] ?? 0) ?>">
            <input type="hidden" name="date_start" value="<?= esc_attr($_GET['date_start'] ?? '') ?>">
            <input type="hidden" name="date_end" value="<?= esc_attr($_GET['date_end'] ?? '') ?>">
            <input type="hidden" name="coupon" value="<?= esc_attr($_GET['coupon'] ?? 0) ?>">
            <?php foreach(DownloadInvoiceInfo::COLUMNS as $column_key => $column_label): ?>
                <input type="checkbox" name="columns[]" value="<?= esc_attr($column_key) ?>" id="column_<?= esc_attr($column_key) ?>" checked>
                <label for="column_<?= esc_attr($column_key) ?>"><?= esc_html($column_label) ?></label>
                <br>
            <?php endforeach; ?>
            <button class="btn btn-warning mt-3" type="submit">Descargar</button>
        </form>
        <?php
        $string = ob_get_clean();
        $this->download_modal->set_body_component(ComponentBuilder::create($string));
    }

    private function render_pagination(): void
    {
        ?>
        <div class="row">
            <div class="col">
                <?php
                $button = $this->download_modal->create_button_launch(ComponentBuilder::create('Descargar en formato CSV <i class="bi bi-download"></i>'));
                $button->set_style(ButtonStyleConstants::WARNING);
                $button->render();
                ?>
            </div>
            <div class="col">
                <div class="pagination-controls">
                    <nav aria-label="Navegación de páginas de facturas">
                        <ul class="pagination justify-content-end">
                            <?php
                            for ($i = 1; $i <= $this->invoice_pagination->total_pages; $i++): ?>
                                <li class="page-item <?= $i === $this->invoice_pagination->current_page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= $this->get_pagination_url($i) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
        <?php
    }

    private function get_pagination_url($page_number): string
    {
        $current_params = $_GET;
        $current_params['page_number'] = $page_number;

        $base_url = strtok($_SERVER['REQUEST_URI'], '?');
        return $base_url . '?' . http_build_query($current_params);
    }
}