<?php
namespace CentralBooking\Profile\Tables;

use CentralBooking\Data\Constants\UserRole;
use CentralBooking\Data\Ticket;
use CentralBooking\GUI\DisplayerInterface;

final class TableOrderTickets implements DisplayerInterface
{
    /**
     * @var array<Ticket>
     */
    private array $tickets;

    public function __construct()
    {
        $this->tickets = git_tickets(['id_order' => isset($_GET['order']) ? (int) $_GET['order'] : 0]);
    }

    public function render()
    {
        if (empty($this->tickets)) {
            return $this->order_empty();
        }
        if (!git_current_user_has_role(UserRole::ADMINISTRATOR)) {
            $current_user = wp_get_current_user();
            $user = $this->tickets[0]->getOrder()->get_user();
            if ($user === false) {
                return $this->order_whitout_owner();
            }
            if ($user->ID !== $current_user->ID) {
                return $this->order_with_owner_mistake();
            }
        }
        return $this->order_info();
    }

    private function order_with_owner_mistake()
    {
        ?>
        <div class="alert alert-danger text-center" role="alert">
            <h5 class="alert-heading">Acceso denegado</h5>
            <p>No tienes permiso para ver los tickets de este pedido, ya que pertenecen a otro usuario.</p>
            <a class="btn btn-secondary mt-2" href="<?= esc_url(remove_query_arg(['order', 'action'])) ?>">
                Volver a la lista de órdenes
            </a>
        </div>
        <?php
    }

    private function order_whitout_owner()
    {
        ?>
        <div class="alert alert-warning text-center" role="alert">
            <h5 class="alert-heading">Pedido sin cliente asignado</h5>
            <p>Este pedido no tiene un cliente asociado. No es posible mostrar la información de los tickets.</p>
            <a class="btn btn-secondary mt-2" href="<?= esc_url(remove_query_arg(['order', 'action'])) ?>">
                Volver a la lista de órdenes
            </a>
        </div>
        <?php
    }

    private function order_info()
    {
        foreach ($this->tickets as $ticket): ?>
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th>Número de ticket:</th>
                        <td>#<?= esc_html($ticket->id); ?></td>
                    </tr>
                    <tr>
                        <th>Fecha de compra:</th>
                        <td><?= git_datetime_format($ticket->getOrder()->get_date_created()->format('Y-m-d H:i:s')); ?></td>
                    </tr>
                    <tr>
                        <th>Pasajeros (<?= esc_html(count($ticket->getPassengers())); ?>):</th>
                        <td>
                            <ul>
                                <?php foreach ($ticket->getPassengers() as $passenger): ?>
                                    <li><?= esc_html($passenger->name); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <th>Flexible:</th>
                        <td>
                            <?php if ($ticket->flexible): ?>
                                <a class="badge bg-primary"
                                    href="<?= add_query_arg(['action' => 'edit_flexible', 'ticket_number' => $ticket->id]) ?>">
                                    Permite editar trayecto
                                </a>
                            <?php else: ?>
                                <span class="badge bg-danger">No permite editar trayecto</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php endforeach;
    }

    private function order_empty()
    {
        ?>
        <p class="m-0">No hay tickets en esta orden. </p>
        <a class="btn btn-primary" href="<?= esc_url(remove_query_arg(['order', 'action'])) ?>">Volver a la lista de
            órdenes</a>
        <?php
    }
}
