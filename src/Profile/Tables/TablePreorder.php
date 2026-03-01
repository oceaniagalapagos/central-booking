<?php
namespace CentralBooking\Profile\Tables;

use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\GUI\DisplayerInterface;

final class TablePreorder implements DisplayerInterface
{
    public function render()
    {
        ?>
        <h3>
            Tus preordenes |
            <a href="<?= esc_url($this->linkCreate()) ?>" class="btn btn-outline-primary">
                <small>
                    Crear
                </small>
            </a>
        </h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>
                        Origen
                    </th>
                    <th>
                        Destino
                    </th>
                    <th>
                        Viaje
                    </th>
                    <th>
                        Pasajeros
                    </th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->fetch() as $ticket): ?>
                    <tr>
                        <td>
                            <?= $ticket->getPassengers()[0]->getRoute()->getOrigin()->name; ?>
                        </td>
                        <td>
                            <?= $ticket->getPassengers()[0]->getRoute()->getDestiny()->name; ?>
                        </td>
                        <td>
                            <?= $ticket->getPassengers()[0]->getDateTrip()->format('d M Y'); ?>,
                            <?= $ticket->getPassengers()[0]->getRoute()->getDepartureTime()->format('H:i'); ?>
                        </td>
                        <td>
                            <?= esc_html(count($ticket->getPassengers())); ?>
                        </td>
                        <td>
                            <a href="<?= esc_url($this->linkEdit($ticket->id)) ?>" class="btn btn-warning">
                                <small>
                                    Ver detalles
                                </small>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    private function linkCreate()
    {
        return add_query_arg(
            [
                'action' => 'create'
            ]
        );
    }

    private function linkEdit(int $id)
    {
        return add_query_arg(
            [
                'action' => 'edit',
                'id' => $id
            ]
        );
    }

    private function fetch()
    {
        $tickets = git_tickets(
            [
                'status' => TicketStatus::PERORDER,
                'id_client' => get_current_user_id(),
            ]
        );
        return $tickets;
    }
}
