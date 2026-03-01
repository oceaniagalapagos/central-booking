<?php
namespace CentralBooking\Admin\Form;

use CentralBooking\Admin\AdminRouter;
use CentralBooking\Admin\View\TablePassengers;
use CentralBooking\Admin\View\TableTickets;
use CentralBooking\Data\Passenger;
use CentralBooking\Data\Ticket;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\Implementation\GUI\PassengerCombine;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

final class FormTicket implements DisplayerInterface
{
    public function render()
    {
        $ticket = $this->loadData();

        $AJAX = add_query_arg(
            ['action' => 'git_edit_ticket'],
            admin_url('admin-ajax.php')
        );
        ?>
        <form action="<?= esc_url($AJAX) ?>" method="post">
            <?php git_nonce_field(); ?>
            <input type="hidden" name="id" value="<?= esc_attr($ticket->id) ?>">
            <h3>| Información de compra</h3>
            <?php $this->ticketInfo($ticket); ?>
            <h3>| Pasajeros</h3>
            <?php foreach ($ticket->getPassengers() as $index => $passenger)
                $this->passengerForm($passenger, $index); ?>
            <button class="button button-primary" type="submit">Guardar</button>
        </form>
        <?php
    }

    private function loadData()
    {
        $id = (int) $_GET['id'] ?? -1;
        $ticket = git_ticket_by_id($id);

        if ($ticket === null) {
            wp_safe_redirect(AdminRouter::get_url_for_class(TableTickets::class));
            exit;
        }

        return $ticket;
    }

    private function ticketInfo(Ticket $ticket)
    {
        $urlOrder = '#';
        if ($ticket->getOrder() !== null) {
            $urlOrder = add_query_arg(
                [
                    'post' => $ticket->getOrder()->get_id(),
                    'action' => 'edit',
                ],
                admin_url('post.php')
            );
        }
        ?>
        <input type="hidden" name="status" value="<?= esc_attr($ticket->status->slug()) ?>">
        <input type="hidden" name="flexible" value="<?= esc_attr($ticket->flexible) ?>">
        <input type="hidden" name="total_amount" value="<?= esc_attr($ticket->total_amount) ?>">
        <table style="margin-bottom: 20px;">
            <?php if ($ticket->getOrder() !== null): ?>
                <tr>
                    <td><strong>Fecha de compra:</strong></td>
                    <td><?= esc_html(git_datetime_format($ticket->getOrder()->get_date_created()->format('Y-m-d H:i:s'))) ?>
                    </td>
                </tr>
            <?php endif; ?>
            <tr>
                <td><strong>Total pagado:</strong></td>
                <td><?= esc_html(git_currency_format($ticket->total_amount)) ?></td>
            </tr>
            <tr>
                <td><strong>Estado:</strong></td>
                <td><?= esc_html($ticket->status->label()) ?></td>
            </tr>
            <tr>
                <td><strong>Cupón:</strong></td>
                <td><?= esc_html($ticket->getCoupon()?->post_title ?? '-') ?></td>
            </tr>
            <?php if ($ticket->getOrder() !== null): ?>
                <tr>
                    <td><strong>Orden de compra:</strong></td>
                    <td>
                        <a target="_blank" href="<?= esc_url($urlOrder) ?>">
                            #<?= esc_html($ticket->getOrder()->get_order_number()) ?>
                        </a>
                    </td>
                </tr>
            <?php endif; ?>
        </table>
        <?php
    }

    private function passengerForm(Passenger $passenger, int $index)
    {
        $form = new PassengerCombine($passenger);
        $inputName = $form->getNameInput("passengers[{$index}][name]");
        $selectNationality = $form->getNationalitySelect("passengers[{$index}][nationality]");
        $selectTypeDocument = $form->getTypeDocumentSelect("passengers[{$index}][type_document]");
        $inputDataDocument = $form->getDataDocumentInput("passengers[{$index}][data_document]");
        $inputBirthday = $form->getBirthdayInput("passengers[{$index}][birthday]");

        $inputName->styles->set('width', '100%');
        $selectNationality->styles->set('width', '100%');
        $selectTypeDocument->styles->set('width', '100%');
        $inputDataDocument->styles->set('width', '100%');
        $inputBirthday->styles->set('width', '100%');

        $inputName->styles->set('border', 'none');
        $inputName->styles->set('border-radius', 0);
        $inputName->styles->set('border-bottom', '2px solid #0073aa');
        $inputName->styles->set('font-size', '16px');

        $passengerLink = AdminRouter::get_url_for_class(TablePassengers::class, [
            'id' => $passenger->id,
        ]);
        $this->showMessage();
        ?>
        <table style="margin-bottom: 20px;">
            <input type="hidden" name="passengers[<?= $index ?>][id]" value="<?= esc_attr($passenger->id) ?>">
            <input type="hidden" name="passengers[<?= $index ?>][type]" value="<?= esc_attr($passenger->type->slug()) ?>">
            <input type="hidden" name="passengers[<?= $index ?>][served]" value="<?= esc_attr($passenger->served) ?>">
            <input type="hidden" name="passengers[<?= $index ?>][approved]" value="<?= esc_attr($passenger->approved) ?>">
            <input type="hidden" name="passengers[<?= $index ?>][route_id]" value="<?= esc_attr($passenger->getRoute()->id) ?>">
            <input type="hidden" name="passengers[<?= $index ?>][transport_id]" value="<?= esc_attr($passenger->getTransport()->id) ?>">
            <tr>
                <td colspan="2">
                    <?php $inputName->render() ?>
                </td>
            </tr>
            <tr>
                <td>
                    <?php $selectNationality->getLabel('Nacionalidad')->render() ?>
                </td>
                <td>
                    <?php $selectNationality->render() ?>
                </td>
            </tr>
            <tr>
                <td>
                    <?php $selectTypeDocument->getLabel('Tipo de documento')->render() ?>
                </td>
                <td>
                    <?php $selectTypeDocument->render() ?>
                </td>
            </tr>
            <tr>
                <td>
                    <?php $inputDataDocument->getLabel('Número de documento')->render() ?>
                </td>
                <td>
                    <?php $inputDataDocument->render() ?>
                </td>
            </tr>
            <tr>
                <td>
                    <?php $inputBirthday->getLabel('Fecha de nacimiento')->render() ?>
                </td>
                <td>
                    <?php $inputBirthday->render() ?>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <a target="_blank" href="<?= esc_url($passengerLink) ?>">
                        <small>Ver registro.</small>
                    </a>
                </td>
            </tr>
        </table>
        <?php
    }

    private function showMessage()
    {
        MessageAlert::getInstance(FormTicket::class)->render();
    }

    public static function writeMessage(string $message, MessageLevel $level = MessageLevel::INFO, int $expiration_seconds = 30)
    {
        (new MessageTemporal)->writeTemporalMessage(
            $message,
            FormTicket::class,
            $level,
            $expiration_seconds
        );
    }
}
