<?php
namespace CentralBooking\Profile\Tables;

use CentralBooking\Data\Constants\UserRole;
use CentralBooking\GUI\DisplayerInterface;
use WP_User;
use WC_Order;

final class TableOrder implements DisplayerInterface
{
    private WP_User $current_user;
    /**
     * @var array<WC_Order>
     */
    private array $orders;
    private int $current_page;
    private int $per_page = 10;
    private int $total_orders;
    private int $total_pages;

    public function __construct()
    {
        $this->current_user = wp_get_current_user();
        $this->current_page = max(1, (int) ($_GET['page_number'] ?? 1));
        $this->total_orders = $this->get_total_orders();
        $this->total_pages = ceil($this->total_orders / $this->per_page);
        $this->orders = $this->get_user_orders();
    }

    public function render()
    {
        ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <?php if (empty($this->orders)): ?>
                        <div class="card">
                            <div class="card-body text-center py-4">
                                <p class="text-muted">No hay pedidos en esta página.</p>
                                <a href="<?= remove_query_arg('paged') ?>" class="btn btn-secondary">
                                    Volver a la primera página
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <?php foreach ($this->orders as $order): ?>
                                        <div class="list-group-item list-group-item-action">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="mb-1">
                                                            <a
                                                                href="<?= add_query_arg(['action' => 'view_order', 'order' => $order->get_id()]) ?>">
                                                                Pedido #<?= $order->get_order_number() ?>
                                                            </a>
                                                        </h6>
                                                        <div class="small text-muted">
                                                            <?= git_datetime_format($order->get_date_created()->format('d-m-Y H:i')) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="mb-1">
                                                        <span class="badge bg-<?= $this->get_status_color($order->get_status()) ?>">
                                                            <?= $this->get_status_label($order->get_status()) ?>
                                                        </span>
                                                    </div>
                                                    <div class="fw-bold">
                                                        <?= git_currency_format($order->get_total(), false) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->total_pages > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($this->current_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= add_query_arg(['page_number' => 1]) ?>">
                                            «
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">
                                            « Inicio
                                        </span>
                                    </li>
                                <?php endif; ?>

                                <?php
                                $start_page = max(1, $this->current_page - 5);
                                $end_page = min($this->total_pages, $this->current_page + 5);

                                if ($end_page - $start_page < 10) {
                                    if ($start_page == 1) {
                                        $end_page = min($this->total_pages, $start_page + 10);
                                    } else {
                                        $start_page = max(1, $end_page - 10);
                                    }
                                }
                                ?>

                                <?php if ($start_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= add_query_arg(['page_number' => 1]) ?>">1</a>
                                    </li>
                                    <?php if ($start_page > 2): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                    <?php if ($i == $this->current_page): ?>
                                        <li class="page-item active">
                                            <span class="page-link"><?= $i ?></span>
                                        </li>
                                    <?php else: ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= add_query_arg(['page_number' => $i]) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($end_page < $this->total_pages): ?>
                                    <?php if ($end_page < $this->total_pages - 1): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link"
                                            href="<?= add_query_arg(['page_number' => $this->total_pages]) ?>"><?= $this->total_pages ?></a>
                                    </li>
                                <?php endif; ?>

                                <?php if ($this->current_page < $this->total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= add_query_arg(['page_number' => $this->total_pages]) ?>">
                                            »
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">
                                            »
                                        </span>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    private function get_user_orders(): array
    {
        if (!is_user_logged_in() || !function_exists('wc_get_orders')) {
            return [];
        }

        $offset = ($this->current_page - 1) * $this->per_page;

        $args = [
            'limit' => $this->per_page,
            'offset' => $offset,
            'orderby' => 'date',
            'order' => 'DESC',
            'status' => 'any'
        ];

        if (!git_current_user_has_role(UserRole::ADMINISTRATOR)) {
            $args['customer_id'] = $this->current_user->ID;
        }

        return wc_get_orders($args);
    }

    private function get_total_orders(): int
    {
        if (!is_user_logged_in() || !function_exists('wc_get_orders')) {
            return 0;
        }

        $orders = wc_get_orders([
            'customer_id' => $this->current_user->ID,
            'limit' => -1,
            'return' => 'ids',
            'status' => 'any'
        ]);

        return count($orders);
    }

    private function get_status_color(string $status): string
    {
        return match ($status) {
            'completed' => 'success',
            'processing' => 'warning',
            'pending' => 'secondary',
            'on-hold' => 'info',
            'cancelled' => 'danger',
            'refunded' => 'dark',
            'failed' => 'danger',
            default => 'primary'
        };
    }

    private function get_status_label(string $status): string
    {
        $labels = [
            'completed' => 'Completado',
            'processing' => 'Procesando',
            'pending' => 'Pendiente',
            'on-hold' => 'En espera',
            'cancelled' => 'Cancelado',
            'refunded' => 'Reembolsado',
            'failed' => 'Fallido'
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    private function get_shop_url(): string
    {
        if (function_exists('wc_get_page_permalink')) {
            return wc_get_page_permalink('shop');
        }

        return home_url();
    }
}
