<?php
namespace CentralBooking\Admin\Setting;

use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\Data\Constants\TypeOperation;
use CentralBooking\Data\Constants\TypeWay;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

final class SettingsLabels implements DisplayerInterface
{
    public const ACTION_NONCE = 'git_settings_nonce';
    public function render()
    {
        $label_route_one_way = new InputComponent('label_route_one_way');
        $label_route_any_way = new InputComponent('label_route_any_way');
        $label_route_double_way = new InputComponent('label_route_double_way');

        $label_transport_a = new InputComponent('label_transport_a');
        $label_transport_b = new InputComponent('label_transport_b');
        $label_transport_c = new InputComponent('label_transport_c');

        $label_ticket_cancel = new InputComponent('label_ticket_cancel');
        $label_ticket_pending = new InputComponent('label_ticket_pending');
        $label_ticket_payment = new InputComponent('label_ticket_payment');
        $label_ticket_partial = new InputComponent('label_ticket_partial');

        $label_route_one_way->setValue(TypeWay::ONE_WAY->label());
        $label_route_any_way->setValue(TypeWay::ANY_WAY->label());
        $label_route_double_way->setValue(TypeWay::DOUBLE_WAY->label());
        $label_transport_a->setValue(TypeOperation::LAND->label());
        $label_transport_b->setValue(TypeOperation::AERO->label());
        $label_transport_c->setValue(TypeOperation::MARINE->label());
        $label_ticket_cancel->setValue(TicketStatus::CANCEL->label());
        $label_ticket_pending->setValue(TicketStatus::PENDING->label());
        $label_ticket_payment->setValue(TicketStatus::PAYMENT->label());
        $label_ticket_partial->setValue(TicketStatus::PARTIAL->label());

        $label_route_one_way->setRequired(true);
        $label_route_any_way->setRequired(true);
        $label_route_double_way->setRequired(true);
        $label_transport_b->setRequired(true);
        $label_transport_a->setRequired(true);
        $label_transport_c->setRequired(true);
        $label_ticket_cancel->setRequired(true);
        $label_ticket_pending->setRequired(true);
        $label_ticket_payment->setRequired(true);
        $label_ticket_partial->setRequired(true);

        $action = add_query_arg(
            ['action' => 'git_settings_labels'],
            admin_url('admin-ajax.php')
        );

        $this->showMessage();
        ?>
        <form action="<?= esc_url($action) ?>" method="post">
            <?php wp_nonce_field(self::ACTION_NONCE) ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th colspan="2">
                        <h2>| Tipos de transporte</h2>
                    </th>
                </tr>
                <tr>
                    <th><?php $label_transport_a->getLabel('Transporte tipo A')->render() ?></th>
                    <td><?php $label_transport_a->render() ?></td>
                </tr>
                <tr>
                    <th><?php $label_transport_b->getLabel('Transporte tipo B')->render() ?></th>
                    <td><?php $label_transport_b->render() ?></td>
                </tr>
                <tr>
                    <th><?php $label_transport_c->getLabel('Transporte tipo C')->render() ?></th>
                    <td><?php $label_transport_c->render() ?></td>
                </tr>
                <tr>
                    <th colspan="2">
                        <h2>| Estados de la ruta</h2>
                    </th>
                <tr>
                    <th><?php $label_ticket_pending->getLabel('Inicial')->render() ?></th>
                    <td><?php $label_ticket_pending->render() ?></td>
                </tr>
                <tr>
                    <th><?php $label_ticket_payment->getLabel('Completado')->render() ?></th>
                    <td><?php $label_ticket_payment->render() ?></td>
                </tr>
                <tr>
                    <th><?php $label_ticket_partial->getLabel('En Proceso')->render() ?></th>
                    <td><?php $label_ticket_partial->render() ?></td>
                </tr>
                <tr>
                    <th><?php $label_ticket_cancel->getLabel('Sin Procesar')->render() ?></th>
                    <td><?php $label_ticket_cancel->render() ?></td>
                </tr>
                <tr>
                    <th colspan="2">
                        <h2>| Tipos de trayecto</h2>
                    </th>
                <tr>
                    <th><?php $label_route_one_way->getLabel('Una vía')->render() ?></th>
                    <td><?php $label_route_one_way->render() ?></td>
                </tr>
                <tr>
                    <th><?php $label_route_double_way->getLabel('Doble vía')->render() ?></th>
                    <td><?php $label_route_double_way->render() ?></td>
                </tr>
                <tr>
                    <th><?php $label_route_any_way->getLabel('Cualquiera')->render() ?></th>
                    <td><?php $label_route_any_way->render() ?></td>
                </tr>
            </table>
            <button type="submit" class="button-primary">
                Guardar
            </button>
        </form>
        <?php
    }

    private function showMessage()
    {
        MessageAlert::getInstance(self::class)->render();
    }

    public static function writeMessage(string $message, MessageLevel $level = MessageLevel::INFO, int $expiration_seconds = 30)
    {
        (new MessageTemporal)->writeTemporalMessage(
            $message,
            self::class,
            $level,
            $expiration_seconds
        );
    }
}