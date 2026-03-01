<?php
namespace CentralBooking\Profile\Forms;

use CentralBooking\Data\Passenger;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\Implementation\GUI\PassengerCombine;

class FormEditPreorder implements DisplayerInterface
{
    public function __construct(private ?int $id = null)
    {
    }

    public function render()
    {
        $ticket = $this->getTicket();
        if ($ticket === null) {
            $this->ticketInvalidContent();
            return;
        }
        ?>
        <h3>
            Detalles de la preorden |
            <a href="<?= esc_url($this->linkList()) ?>" class="btn btn-outline-primary">
                <small>
                    Ver lista
                </small>
            </a>
        </h3>
        <form method="post" action="<?= esc_url($this->linkAjax()) ?>">
            <?php wp_nonce_field('edit_ticket', 'nonce') ?>
            <input type="hidden" name="route" value="<?= esc_attr($ticket->getPassengers()[0]->getRoute()->id) ?>">
            <input type="hidden" name="transport" value="<?= esc_attr($ticket->getPassengers()[0]->getTransport()->id) ?>">
            <table class="table table-bordered">
                <tr>
                    <th>Ruta</th>
                    <td>
                        <?= esc_html($ticket->getPassengers()[0]->getRoute()->getOrigin()->name); ?> »
                        <?= esc_html($ticket->getPassengers()[0]->getRoute()->getDestiny()->name); ?>
                    </td>
                </tr>
                <tr>
                    <th>Viaje</th>
                    <td>
                        <?= esc_html($ticket->getPassengers()[0]->getDateTrip()->format('d M Y')); ?>,
                        <?= esc_html($ticket->getPassengers()[0]->getRoute()->getDepartureTime()->format('H:i')); ?>
                    </td>
                </tr>
            </table>
            <?php
            foreach ($ticket->getPassengers() as $index => $passenger) {
                $this->formPassenger($passenger, $index);
            }
            ?>
            <button type="submit" class="btn btn-primary">
                Guardar cambios
            </button>
        </form>
        <?php
    }

    private function formPassenger(Passenger $passenger, int $index = 0)
    {
        $formPassenger = new PassengerCombine($passenger);

        $inputName = $formPassenger->getNameInput("passengers[{$index}][name]");
        $inputBirthday = $formPassenger->getBirthdayInput("passengers[{$index}][birthday]");
        $nationalitySelect = $formPassenger->getNationalitySelect("passengers[{$index}][nationality]");
        $inputDataDocument = $formPassenger->getDataDocumentInput("passengers[{$index}][data_document]");
        $selectTypeDocument = $formPassenger->getTypeDocumentSelect("passengers[{$index}][type_document]");

        $inputName->setPlaceholder('Nombre...');
        $inputName->styles->set('border', 'none');
        $inputName->styles->set('border-bottom', '2px solid gray');
        $inputName->class_list->add('fs-3', 'fw-bold');

        $inputName->render();
        ?>
        <table class="table table-bordered mb-5">
            <tr>
                <td>
                    <?= $selectTypeDocument->getLabel('Tipo de documento')->compact(); ?>
                </td>
                <td>
                    <?= $selectTypeDocument->compact(); ?>
                </td>
                <td>
                    <?= $inputDataDocument->getLabel('Número de documento')->compact(); ?>
                </td>
                <td>
                    <?= $inputDataDocument->compact(); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <?= $nationalitySelect->getLabel('Nacionalidad')->compact(); ?>
                </td>
                <td>
                    <?= $nationalitySelect->compact(); ?>
                </td>
                <td>
                    <?= $inputBirthday->getLabel('Fecha de nacimiento')->compact(); ?>
                </td>
                <td>
                    <?= $inputBirthday->compact(); ?>
                </td>
            </tr>
        </table>
        <?php
    }

    private function ticketInvalidContent()
    {
        ?>
        <p>Preorden no encontrada.</p>
        <?php
    }

    private function getTicket()
    {
        if ($this->id === null) {
            return null;
        }
        $ticket = git_ticket_by_id($this->id);
        return $ticket;
    }

    private function linkList()
    {
        return add_query_arg(
            [
                'action' => 'list'
            ]
        );
    }

    private function linkAjax()
    {
        return add_query_arg(
            ['action' => 'git_edit_ticket'],
            admin_url('admin-ajax.php')
        );
    }
}
