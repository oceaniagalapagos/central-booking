<?php
namespace CentralBooking\Profile\Forms;

use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\Implementation\GUI\SelectorRouteCombine;

class FormPreorder implements DisplayerInterface
{
    public function render()
    {
        $combine = new SelectorRouteCombine();
        $dateTripInput = git_date_trip_field('date_trip');
        $passengersInput = git_input_field(['name' => 'passengers', 'type' => 'number']);
        $timeSelect = $combine->get_time_select('departure_time');
        $originSelect = $combine->get_origin_select();
        $destinySelect = $combine->get_destiny_select();
        $transportSelect = $combine->get_transport_select();
        $passengersInput->attributes->set('min', '1');
        $passengersInput->attributes->set('value', '1');
        $passengersInput->setRequired(true);
        $dateTripInput->setRequired(true);
        $action = add_query_arg(
            [
                'action' => 'git_edit_ticket_operator',
            ],
            admin_url('admin-ajax.php')
        );
        ?>
        <h3>
            Crea una preorden |
            <a href="<?= esc_url($this->linkList()) ?>" class="btn btn-outline-primary">
                <small>
                    Ver lista
                </small>
            </a>
        </h3>
        <form method="post" action="<?= esc_url($action); ?>">
            <?php git_nonce_field(); ?>
            <?php git_referer_field(); ?>
            <table class="table table-bordered">
                <tr>
                    <td>
                        <?= $originSelect->getLabel('Origen')->compact(); ?>
                    </td>
                    <td>
                        <?= $originSelect->compact(); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?= $destinySelect->getLabel('Destino')->compact(); ?>
                    </td>
                    <td>
                        <?= $destinySelect->compact(); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?= $timeSelect->getLabel('Hora')->compact(); ?>
                    </td>
                    <td>
                        <?= $timeSelect->compact(); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?= $transportSelect->getLabel('Transporte')->compact(); ?>
                    </td>
                    <td>
                        <?= $transportSelect->compact(); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?= $dateTripInput->getLabel('Fecha de viaje')->compact(); ?>
                    </td>
                    <td>
                        <?= $dateTripInput->compact(); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?= $passengersInput->getLabel('Pasajeros')->compact(); ?>
                    </td>
                    <td>
                        <?= $passengersInput->compact(); ?>
                    </td>
                </tr>
            </table>
            <button class="btn btn-primary" type="submit">Crear ticket</button>
        </form>
        <?php
    }

    private function linkList()
    {
        return add_query_arg(
            [
                'action' => 'list'
            ]
        );
    }
}
