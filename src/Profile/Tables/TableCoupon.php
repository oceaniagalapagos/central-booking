<?php
namespace CentralBooking\Profile\Tables;

use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\Data\Services\TicketService;
use CentralBooking\GUI\DisplayerInterface;

final class TableCoupon implements DisplayerInterface
{
    private string $danger_hex = '#F8D7DA';
    private string $warning_hex = '#FFF3CD';
    private string $success_hex = '#D1E7DD';
    private string $base_hex = '#F8F9FA';
    private int $total_pages;
    private int $current_page;

    private function fetch_tickets()
    {
        $coupon = $_GET['coupon'] ?? null;
        $date_end = $_GET['date_end'] ?? null;
        $date_start = $_GET['date_start'] ?? null;
        $this->total_pages = 0;
        $this->current_page = 0;

        $tickets = [];

        if ($coupon && $date_start && $date_end) {

            $service = new TicketService();

            $result = $service->find(
                [
                    'id_coupon' => $coupon,
                    'date_creation_from' => $date_start,
                    'date_creation_to' => $date_end,
                ],
                'date_creation',
                'DESC',
                -1
            );

            $tickets = $result->getItems();
            $this->total_pages = $result->getTotalPages();
            $this->current_page = $result->getCurrentPage();
        }
        return $tickets;
    }
    public function render()
    {
        $tickets = $this->fetch_tickets();
        ?>
        <div class="git-profile-section">
            <!-- Leyenda de estados -->
            <div class="git-legend-container">
                <h4 class="git-legend-title">Leyenda de estados:</h4>
                <div class="git-legend-grid">
                    <div class="git-legend-item">
                        <div class="git-legend-indicator git-status-pending"></div>
                        <span class="git-legend-label"><?= TicketStatus::PENDING->label() ?></span>
                    </div>
                    <div class="git-legend-item">
                        <div class="git-legend-indicator git-status-payment"></div>
                        <span class="git-legend-label"><?= TicketStatus::PAYMENT->label() ?></span>
                    </div>
                    <div class="git-legend-item">
                        <div class="git-legend-indicator git-status-partial"></div>
                        <span class="git-legend-label"><?= TicketStatus::PARTIAL->label() ?></span>
                    </div>
                    <div class="git-legend-item">
                        <div class="git-legend-indicator git-status-cancel"></div>
                        <span class="git-legend-label"><?= TicketStatus::CANCEL->label() ?></span>
                    </div>
                </div>
            </div>

            <!-- Tabla de resultados -->
            <div class="git-profile-card">
                <div class="git-profile-card-header">
                    <h3 class="git-card-title">
                        Resultados de búsqueda
                        <span class="git-results-count">(<?= count($tickets) ?> tickets encontrados)</span>
                    </h3>
                </div>
                <div class="git-profile-card-body">
                    <?php if (empty($tickets)): ?>
                        <div class="git-empty-state">
                            <div class="git-empty-icon">
                                <i class="dashicons dashicons-search"></i>
                            </div>
                            <p class="git-empty-message">No se encontraron tickets con los criterios especificados.</p>
                            <p class="git-empty-hint">Intenta ajustar los filtros de búsqueda.</p>
                        </div>
                    <?php else: ?>
                        <div class="git-table-wrapper">
                            <table class="git-table">
                                <thead>
                                    <tr>
                                        <th class="git-th-action">Acción</th>
                                        <th class="git-th-ticket">Nro. Ticket</th>
                                        <th class="git-th-order">Pedido</th>
                                        <th class="git-th-date">Fecha de compra</th>
                                        <th class="git-th-total">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tickets as $ticket):
                                        $status_classes = [
                                            TicketStatus::PENDING->name => 'git-row-pending',
                                            TicketStatus::PAYMENT->name => 'git-row-payment',
                                            TicketStatus::PARTIAL->name => 'git-row-partial',
                                            TicketStatus::CANCEL->name => 'git-row-cancel',
                                        ];
                                        $row_class = $status_classes[$ticket->status->name] ?? 'git-row-pending';
                                        ?>
                                        <tr class="<?= $row_class ?> git-status-<?= strtolower($ticket->status->name) ?>">
                                            <td>
                                                <a href="<?= esc_url(
                                                    add_query_arg(
                                                        [
                                                            'action' => 'edit',
                                                            'id' => $ticket->id
                                                        ],
                                                        remove_query_arg([
                                                            'coupon',
                                                            'date_start',
                                                            'date_end'
                                                        ])
                                                    )
                                                ) ?>">
                                                    Editar
                                                </a>
                                            </td>
                                            <td>
                                                <span>#<?= $ticket->id ?></span>
                                            </td>
                                            <td>
                                                <span>#<?= $ticket->getOrder()->get_id() ?></span>
                                            </td>
                                            <td>
                                                <?= git_datetime_format($ticket->getOrder()->get_date_created()) ?>
                                            </td>
                                            <td>
                                                <span><?= git_currency_format($ticket->total_amount, true) ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <?php if ($this->total_pages > 1): ?>
                            <div class="git-pagination-wrapper">
                                <nav class="git-pagination" aria-label="Navegación de páginas">
                                    <ul class="git-pagination-list">
                                        <?php for ($i = 1; $i <= $this->total_pages; $i++): ?>
                                            <li class="git-pagination-item">
                                                <a class="git-pagination-link <?= $i === $this->current_page ? 'git-pagination-current' : '' ?>"
                                                    href="<?= add_query_arg('page_number', $i) ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <style>
            /* Leyenda de estados */
            .git-legend-container {
                margin-bottom: 20px;
                padding: 16px;
                background: #f9fafb;
                border: 1px solid #e5e7eb;
                border-radius: 6px;
            }

            .git-legend-title {
                margin: 0 0 12px 0;
                font-size: 14px;
                font-weight: 600;
                color: #374151;
            }

            .git-legend-grid {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;
            }

            .git-legend-item {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .git-legend-indicator {
                width: 16px;
                height: 16px;
                border-radius: 3px;
                border: 1px solid rgba(0, 0, 0, 0.1);
            }

            .git-status-pending {
                background-color:
                    <?= $this->base_hex ?>
                ;
            }

            .git-status-payment {
                background-color:
                    <?= $this->success_hex ?>
                ;
            }

            .git-status-partial {
                background-color:
                    <?= $this->warning_hex ?>
                ;
            }

            .git-status-cancel {
                background-color:
                    <?= $this->danger_hex ?>
                ;
            }

            .git-legend-label {
                font-size: 14px;
                color: #6b7280;
            }

            /* Tabla */
            .git-table-wrapper {
                overflow-x: auto;
            }

            .git-table {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0;
                border: 1px solid #e5e7eb;
                border-radius: 6px;
                overflow: hidden;
            }

            .git-table thead th {
                background: #f9fafb;
                color: #374151;
                font-weight: 600;
                font-size: 14px;
                padding: 12px 16px;
                text-align: left;
                border-bottom: 1px solid #e5e7eb;
                white-space: nowrap;
            }

            .git-table tbody td {
                padding: 12px 16px;
                border-bottom: 1px solid #e5e7eb;
                font-size: 14px;
            }

            .git-table tbody tr:last-child td {
                border-bottom: none;
            }

            /* Estados de filas */
            .git-row-pending {
                background: rgba(248, 215, 218, 0.1);
            }

            .git-row-payment {
                background: rgba(209, 231, 221, 0.1);
            }

            .git-row-partial {
                background: rgba(255, 243, 205, 0.1);
            }

            .git-row-cancel {
                background: rgba(248, 215, 218, 0.15);
            }

            /* Badges de estado */
            .git-status-badge {
                padding: 4px 8px;
                border-radius: 12px;
                font-size: 12px;
                font-weight: 500;
                text-transform: uppercase;
            }

            .git-status-pending {
                background:
                    <?= $this->base_hex ?>
                ;
                color: #6b7280;
                border: 1px solid #d1d5db;
            }

            .git-status-payment {
                background:
                    <?= $this->success_hex ?>
                ;
                color: #166534;
                border: 1px solid #22c55e;
            }

            .git-status-partial {
                background:
                    <?= $this->warning_hex ?>
                ;
                color: #92400e;
                border: 1px solid #f59e0b;
            }

            .git-status-cancel {
                background:
                    <?= $this->danger_hex ?>
                ;
                color: #991b1b;
                border: 1px solid #ef4444;
            }

            /* Botón pequeño */
            .git-btn-sm {
                padding: 6px 12px;
                font-size: 12px;
            }

            /* Elementos especiales */
            .git-ticket-id,
            .git-order-id {
                font-family: 'Courier New', monospace;
                font-weight: 600;
                color: #3b82f6;
            }

            .git-amount {
                font-weight: 600;
                color: #059669;
            }

            .git-results-count {
                font-weight: 400;
                font-size: 14px;
                color: #6b7280;
            }

            /* Estado vacío */
            .git-empty-state {
                text-align: center;
                padding: 60px 20px;
            }

            .git-empty-icon {
                width: 64px;
                height: 64px;
                border-radius: 50%;
                background: #f3f4f6;
                color: #9ca3af;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 16px;
            }

            .git-empty-icon .dashicons {
                font-size: 24px;
            }

            .git-empty-message {
                font-size: 16px;
                color: #374151;
                margin: 0 0 8px 0;
            }

            .git-empty-hint {
                font-size: 14px;
                color: #6b7280;
                margin: 0;
            }

            /* Paginación */
            .git-pagination-wrapper {
                margin-top: 24px;
                padding-top: 16px;
                border-top: 1px solid #e5e7eb;
            }

            .git-pagination {
                display: flex;
                justify-content: center;
            }

            .git-pagination-list {
                display: flex;
                list-style: none;
                margin: 0;
                padding: 0;
                gap: 4px;
            }

            .git-pagination-link {
                display: block;
                padding: 8px 12px;
                color: #6b7280;
                text-decoration: none;
                border: 1px solid #e5e7eb;
                border-radius: 4px;
                font-size: 14px;
                transition: all 0.2s ease;
            }

            .git-pagination-link:hover {
                background: #f9fafb;
                color: #374151;
                text-decoration: none;
            }

            .git-pagination-current {
                background: #3b82f6 !important;
                color: white !important;
                border-color: #3b82f6 !important;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .git-legend-grid {
                    flex-direction: column;
                    gap: 12px;
                }

                .git-table thead {
                    display: none;
                }

                .git-table tbody tr {
                    display: block;
                    border: 1px solid #e5e7eb;
                    border-radius: 6px;
                    margin-bottom: 12px;
                    padding: 16px;
                }

                .git-table tbody td {
                    display: block;
                    padding: 4px 0;
                    border: none;
                    text-align: left;
                }

                .git-table tbody td:before {
                    content: attr(data-label) ': ';
                    font-weight: 600;
                    color: #374151;
                    margin-right: 8px;
                }

                .git-btn-sm {
                    width: 100%;
                    margin-top: 8px;
                }
            }
        </style>
        <?php
    }
}
