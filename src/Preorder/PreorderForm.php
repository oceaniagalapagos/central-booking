<?php
namespace CentralBooking\Preorder;

use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\Data\Passenger;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\Implementation\GUI\SelectorRouteCombine;
use CentralBooking\Implementation\GUI\TypeDocumentSelect;

final class PreorderForm implements DisplayerInterface
{
    public const NONCE_ACTION = 'central_booking_preorder_form';

    public function render()
    {
        $ticket = $this->loadData();
        $ajax = add_query_arg(
            ['action' => 'git_edit_ticket'],
            admin_url('admin-ajax.php')
        );
        ?>
        <h2 class="my-3">Número de preorden #<?= $ticket->id ?></h2>
        <form action="<?= esc_url($ajax) ?>" method="post">
            <input type="hidden" name="id" value="<?= $ticket->id ?>">
            <input type="hidden" name="status" value="<?= TicketStatus::PENDING->slug() ?>">
            <?php wp_nonce_field(self::NONCE_ACTION); ?>
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <td>
                            <?php $this->formTrip(); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php foreach ($ticket->getPassengers() as $index => $passenger): ?>
                                <?= $index === 0 ? '' : '<hr>' ?>
                                <p class="my-2 fw-bold">Pasajero <?= $index + 1 ?></p>
                                <?php $this->formPassenger($passenger, $index); ?>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table class="table table-bordered">
                                <tr>
                                    <td>Servicios extras</td>
                                </tr>
                                <tr>
                                    <td>
                                        <label for="input-carga">Carga</label>
                                        <input id="input-carga" type="number" name="extra[carga]" value="0" min="0">
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <label for="input-flexible">Flexible</label>
                                        <input id="input-flexible" type="checkbox" name="extra[flexible]" checked>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
            <button class="btn btn-primary" type="submit">Enviar</button>
        </form>
        <?php
    }

    private function formTrip()
    {
        $routeCombine = new SelectorRouteCombine();

        $originSelect = $routeCombine->get_origin_select('origin_id');
        $destinySelect = $routeCombine->get_destiny_select('destiny_id');
        $transportSelect = $routeCombine->get_transport_select('transport_id');
        $departureTimeSelect = $routeCombine->get_time_select('departure_time');
        $dateTripSelect = git_date_trip_field('date_trip');

        ?>
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <td>
                        <?php $originSelect->getLabel('Origen')->render() ?>
                    </td>
                    <td>
                        <?php $originSelect->render() ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php $destinySelect->getLabel('Destino')->render() ?>
                    </td>
                    <td>
                        <?php $destinySelect->render() ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php $departureTimeSelect->getLabel('Hora de salida')->render() ?>
                    </td>
                    <td>
                        <?php $departureTimeSelect->render() ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php $transportSelect->getLabel('Transporte')->render() ?>
                    </td>
                    <td>
                        <?php $transportSelect->render() ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php $dateTripSelect->getLabel('Fecha de viaje')->render() ?>
                    </td>
                    <td>
                        <?php $dateTripSelect->render() ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    private function formPassenger(Passenger $passenger, int $index)
    {
        $nameInput = new InputComponent("passengers[$index][name]", 'text');
        $dataDocumentInput = new InputComponent("passengers[$index][data_document]");
        $birthdayInput = new InputComponent("passengers[$index][birthday]", 'date');
        $nationalityInput = git_country_select_field("passengers[$index][nationality]");
        $typeDocumentInput = (new TypeDocumentSelect("passengers[$index][type_document]"))->create();

        $nameInput->setRequired(true);
        $birthdayInput->setRequired(true);
        $nationalityInput->setRequired(true);
        $dataDocumentInput->setRequired(true);
        $typeDocumentInput->setRequired(true);

        $nameInput->setValue($passenger->name);
        $nationalityInput->setValue($passenger->nationality);
        $dataDocumentInput->setValue($passenger->dataDocument);
        $typeDocumentInput->setValue($passenger->typeDocument);
        $birthdayInput->setValue($passenger->getBirthday()->format('Y-m-d'));

        ?>
        <input type="hidden" name="passengers[<?= $index ?>][id]" value="<?= $passenger->id ?>">
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <td>
                        <?php $nameInput->getLabel('Nombre')->render() ?>
                    </td>
                    <td>
                        <?php $nameInput->render() ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php $nationalityInput->getLabel('Nacionalidad')->render() ?>
                    </td>
                    <td>
                        <?php $nationalityInput->render() ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php $typeDocumentInput->getLabel('Tipo de documento')->render() ?>
                    </td>
                    <td>
                        <?php $typeDocumentInput->render() ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php $dataDocumentInput->getLabel('Número de documento')->render() ?>
                    </td>
                    <td>
                        <?php $dataDocumentInput->render() ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php $birthdayInput->getLabel('Fecha de nacimiento')->render() ?>
                    </td>
                    <td>
                        <?php $birthdayInput->render() ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    private function loadData()
    {
        $id = (int) ($_GET['preorder'] ?? -1);
        $ticket = git_ticket_by_id($id);
        if ($ticket === null) {
            PreorderDashboard::writeMessage('No se encontró ningún ticket.');
            $url = remove_query_arg(['preorder']);
            wp_safe_redirect($url);
            exit;
        }
        return $ticket;
    }
}
