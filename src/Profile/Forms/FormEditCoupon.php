<?php
namespace CentralBooking\Profile\Forms;

use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\Data\Constants\UserRole;
use CentralBooking\Data\Operator;
use CentralBooking\Data\Ticket;
use CentralBooking\GUI\ButtonComponent;
use CentralBooking\GUI\Constants\ButtonActionConstants;
use CentralBooking\GUI\Constants\ButtonStyleConstants;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

class FormEditCoupon implements DisplayerInterface
{
    public const ACTION_NONCE = 'git_edit_ticket_status';

    public function render()
    {
        if (!$this->canEdit()) {
            $this->panelCantEdit();
        } else {
            $ticket = $this->loadData();
            ?>
            <a id="link_to_search_pane" class="btn btn-primary mb-3"
                href="<?= esc_url(remove_query_arg(['action', 'ticket_id'])) ?>">
                <i class="bi bi-arrow-left"></i> Regresar al buscador
            </a>
            <?php
            $this->panelInfo($ticket);
            $this->panelFormTicket($ticket);
        }
    }

    private function loadData()
    {
        $id = (int) ($_GET['id'] ?? 0);
        return git_ticket_by_id($id);
    }

    private function canEdit()
    {
        if (!git_user_logged_in()) {
            return false;
        }

        if (!isset($_GET['id'])) {
            return false;
        }

        $ticket_id = (int) $_GET['id'];
        $ticket = git_ticket_by_id($ticket_id);

        if ($ticket === null) {
            return false;
        }

        $coupon = $ticket->getCoupon();

        if ($coupon === null) {
            return false;
        }

        if (git_current_user_has_role(UserRole::ADMINISTRATOR)) {
            return true;
        }

        if (!git_current_user_has_role(UserRole::OPERATOR)) {
            return false;
        }

        $operator = new Operator();
        $operator->setUser(wp_get_current_user());
        $coupons = $operator->getCoupons();

        foreach ($coupons as $coupon_operator) {
            if ($coupon_operator->ID === $coupon->ID) {
                return true;
            }
        }

        return false;
    }

    private function panelCantEdit()
    {
        $url_search = esc_url(remove_query_arg(['action', 'id']));
        ?>
        <p>No puedes editar este ticket por una de las siguientes razones:</p>
        <ul>
            <li>No estás logueado.</li>
            <li>El ticket no existe.</li>
            <li>El ticket no tiene un cupón asignado.</li>
            <li>No tienes permisos para editar el cupón asignado al ticket.</li>
        </ul>
        <p>Si crees que esto es un error, por favor contacta con el soporte. Caso contrario, intenta <a
                href="<?= $url_search ?>">buscar</a> otro ticket.</p>
        <?php
    }

    private function panelInfo(Ticket $ticket)
    {
        ?>
        <table class="table table-bordered">
            <tr>
                <td><b>Código de cupon:</b></td>
                <td><?= $ticket->getCoupon()->post_title ?></td>
            </tr>
            <tr>
                <td><b>Precio:</b></td>
                <td><?= git_currency_format($ticket->total_amount, true) ?></td>
            </tr>
            <tr>
                <td><b>Fecha de compra:</b></td>
                <td><?= git_datetime_format($ticket->getOrder()->get_date_created()->format('Y-m-d H:i:s')) ?></td>
            </tr>
            <tr>
                <td><b>Número de ticket:</b></td>
                <td><?= $ticket->id ?></td>
            </tr>
        </table>
        <?php
    }

    private function panelFormTicket(Ticket $ticket)
    {
        $status_select = git_ticket_status_select_field('status');
        $code_input = git_input_field([
            'name' => 'code',
            'required' => true,
        ]);
        $amount_input = git_input_field([
            'name' => 'amount',
            'required' => true,
            'type' => 'number',
        ]);
        $file_input = git_file_field('proof');
        $button_submit = new ButtonComponent('Guardar', ButtonActionConstants::SUBMIT, ButtonStyleConstants::PRIMARY);
        $button_submit->id = 'button_submit_' . time();

        $amount_input->attributes->set('min', '0');
        $file_input->styles->set('display', 'none');

        $proof_payment = $ticket->getProofPayment();
        $status_select->setValue($ticket->status->slug());

        if (!git_current_user_has_role(UserRole::ADMINISTRATOR) && $ticket->status !== TicketStatus::PENDING) {
            $status_select->attributes->set('disabled', '');
            $code_input->attributes->set('disabled', '');
        }
        if (($proof_payment === null) === false) {
            $code_input->setValue($proof_payment->code);
            $amount_input->setValue(floatval($proof_payment->amount) / 100);
            $amount_input->attributes->set('max', $ticket->total_amount / 100);
            $amount_input->attributes->set('step', 0.1);
        }
        $action = esc_url(
            add_query_arg(
                ['action' => 'git_edit_ticket_status'],
                admin_url('admin-ajax.php')
            )
        );
        $canEdit = git_current_user_has_role(UserRole::ADMINISTRATOR) || $ticket->status === TicketStatus::PENDING;
        $this->showMessage();
        ?>
        <form id="form_edit_ticket" method="post" action="<?= $action ?>" enctype="multipart/form-data">
            <?php wp_nonce_field(self::ACTION_NONCE, 'nonce');
            $file_input->render(); ?>
            <input type="hidden" name="id" value="<?= $ticket->id ?>">
            <div class="mb-3">
                <?= $status_select->getLabel('Estado')->compact(); ?>
                <?= $status_select->compact(); ?>
                <div style="<?= $ticket->status === TicketStatus::PARTIAL ? 'display: block;' : 'display: none;' ?>"
                    id="partial-options-container">
                    <div class="mb-3 p-3 bg-success bg-opacity-25 border border-success rounded">
                        <span class="fs-4 fw-medium">Pasajeros aprobados a viajar:</span>
                        <div id="approved-passengers-container">
                            <?php foreach ($ticket->getPassengers() as $passenger):
                                $check = new InputComponent(
                                    'passengers[]',
                                    'checkbox'
                                );
                                $check->class_list->remove('form-control');
                                $check->setValue($passenger->id);
                                if ($passenger->approved)
                                    $check->attributes->set('checked', true);
                                if ($canEdit === false)
                                    $check->attributes->set('disabled', true);
                                ?>
                                <div class="form-check">
                                    <?= $check->compact() ?>
                                    <?= $check->getLabel(esc_html($passenger->name))->compact() ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <?= $amount_input->getLabel('Monto')->compact(); ?>
                        <?= $amount_input->compact(); ?>
                    </div>
                </div>
            </div>
            <div id="section-payment">
                <div class="mb-3">
                    <?= $code_input->getLabel('Código')->compact(); ?>
                    <?= $code_input->compact(); ?>
                </div>
                <hr>
                <div class="mb-3">
                    <div class="m-3">
                        <?= $file_input->getLabel($proof_payment === null ? 'Subir archivo...' : $proof_payment->filename)->compact() ?>
                    </div>
                    <div class="btn-group">
                        <?php if ($proof_payment !== null): ?>
                            <a class="btn btn-outline-success" href="<?= $proof_payment->url ?? '#' ?>" target="_blank">
                                <i class="bi bi-eye"></i> Recupera el comprobante
                            </a>
                        <?php endif; ?>
                        <button id="button_upload_proof_payment" type="button" class="btn btn-outline-primary">
                            <i class="bi bi-upload"></i> Sube el comprobante
                        </button>
                    </div>
                </div>
            </div>
            <?php
            if ($canEdit) {
                $button_submit->render();
            }
            ?>
        </form>
        <script>
            const selectElement = document.getElementById('<?= $status_select->id ?>');
            const submitButton = document.getElementById('<?= $button_submit->id ?>');
            const fileInput = document.getElementById('<?= $file_input->id ?>');
            selectElement.addEventListener('change', function (event) {
                const selectedValue = event.target.value;
                if (['<?= TicketStatus::CANCEL->slug() ?>', '<?= TicketStatus::PENDING->slug() ?>'].includes(selectedValue)) {
                    document.getElementById('section-payment').style.display = 'none';
                    document.getElementById('<?= $code_input->id ?>').required = false;
                    document.getElementById('partial-options-container').style.display = 'none';
                } else if (selectedValue === '<?= TicketStatus::PARTIAL->slug() ?>') {
                    document.getElementById('partial-options-container').style.display = 'block';
                } else {
                    document.getElementById('partial-options-container').style.display = 'none';
                    document.getElementById('section-payment').style.display = 'block';
                    document.getElementById('<?= $code_input->id ?>').required = true;
                }
            });
            selectElement.dispatchEvent(new Event('change'));
            document.getElementById('button_upload_proof_payment').addEventListener('click', function () {
                document.getElementById('<?= $file_input->id ?>').click();
            });
            document.getElementById('<?= $file_input->id ?>').addEventListener('change', function (event) {
                const fileName = event.target.files[0]?.name || 'Subir archivo...';
                const label = document.querySelector("label[for='<?= $file_input->id ?>']");
                label.textContent = fileName;
            });
            document.getElementById('form_edit_ticket').addEventListener('submit', event => {
                submitButton.textContent = 'Guardando...';
                submitButton.disabled = true;
                if (fileInput.files.length === 0) {
                    fileInput.remove();
                }
            });
        </script>
        <?php
    }

    private function showMessage()
    {
        MessageAlert::getInstance(FormEditCoupon::class)->render();
    }

    public static function writeMessage(string $message, MessageLevel $level = MessageLevel::INFO, int $expiration_seconds = 30)
    {
        (new MessageTemporal())->writeTemporalMessage(
            $message,
            FormEditCoupon::class,
            $level,
            $expiration_seconds
        );
    }
}
