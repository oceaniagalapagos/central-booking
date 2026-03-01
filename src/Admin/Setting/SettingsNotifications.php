<?php
namespace CentralBooking\Admin\Setting;

use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\Data\Ticket;
use CentralBooking\GUI\AccordionComponent;
use CentralBooking\GUI\CodeEditorComponent;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;
use CentralBooking\Placeholders\PlaceholderEngineCheckout;
use CentralBooking\Placeholders\PlaceholderEngineTicket;
use WC_Order;

final class SettingsNotifications implements DisplayerInterface
{
    public const ACTION_NONCE = 'git_settings_nonce';
    private InputComponent $notification_checkout_email_title;
    private InputComponent $notification_checkout_email_sender;
    private CodeEditorComponent $notification_checkout_message;
    private CodeEditorComponent $notification_checkout_email_content;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $this->notification_checkout_message = new CodeEditorComponent('notification_checkout_message');
        $this->notification_checkout_email_title = new InputComponent('notification_checkout_email_title');
        $this->notification_checkout_email_sender = new InputComponent('notification_checkout_email_sender');
        $this->notification_checkout_email_content = new CodeEditorComponent('notification_checkout_email_content');

        $this->notification_checkout_email_sender->styles->set('text-align', 'end');
        foreach ([$this->notification_checkout_email_content, $this->notification_checkout_message] as $code_editor) {
            $code_editor->set_language('html');
            $code_editor->styles->set('width', '100%');
            $code_editor->attributes->set('rows', 7);
        }

        $this->notification_checkout_message->setValue(git_get_setting(SettingsKeys::NOTIFICATION_CHECKOUT_MESSAGE, ''));
        $this->notification_checkout_email_title->setValue(git_get_map_setting(SettingsKeys::NOTIFICATION_CHECKOUT_EMAIL_TITLE, ''));
        $this->notification_checkout_email_sender->setValue(git_get_map_setting(SettingsKeys::NOTIFICATION_CHECKOUT_EMAIL_SENDER, ''));
        $this->notification_checkout_email_content->setValue(git_get_map_setting(SettingsKeys::NOTIFICATION_CHECKOUT_EMAIL_CONTENT, ''));
    }

    public function render()
    {
        $accordion = new AccordionComponent();
        $accordion->styles->set('margin-top', '20px');
        $accordion->addItem(
            git_string_to_component('<i class="bi bi-bookmark"></i> Placeholders (Ticket)'),
            (new PlaceholderEngineTicket(new Ticket()))->get_placeholders_info()
        );
        $accordion->addItem(
            git_string_to_component('<i class="bi bi-bookmark"></i> Placeholders (Pedido)'),
            (new PlaceholderEngineCheckout(new WC_Order()))->get_placeholders_info(),
        );
        $accordion->render();
        $action = add_query_arg(
            ['action' => 'git_settings_notifications'],
            admin_url('admin-ajax.php')
        );
        $this->showMessage();
        ?>
        <form action="<?= esc_url($action) ?>" method="post">
            <?php wp_nonce_field(self::ACTION_NONCE); ?>
            <h3>Mensaje de checkout</h3>
            <p>Este mensaje aparecerá en la pantalla de <i>Thank You</i> de WooCommerce.</p>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <?php $this->notification_checkout_message->getLabel('Mensaje de Thank You')->render(); ?>
                        <br>
                        <small>Placeholders (Pedido)</small>
                    </th>
                    <td>
                        <?php $this->notification_checkout_message->render(); ?>
                    </td>
                </tr>
            </table>
            <hr>
            <h3>Email de confirmación</h3>
            <p>
                Se notificará al cliente dueño del ticket cuando su ticket haya cambiado a uno de los siguientes estados:
                <code><?= TicketStatus::PAYMENT->label() ?></code>,
                <code><?= TicketStatus::PARTIAL->label() ?></code>
            </p>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <?php $this->notification_checkout_email_title->getLabel('Título')->render() ?>
                    </th>
                    <td>
                        <?php $this->notification_checkout_email_title->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->notification_checkout_email_sender->getLabel('Remitente')->render() ?>
                    </th>
                    <td>
                        <?php
                        $this->notification_checkout_email_sender->render();
                        $url = get_site_url();
                        $parsed = parse_url($url);
                        ?>
                        <code><?= '@' . ($parsed['host'] ?? $url) ?></code>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->notification_checkout_email_content->getLabel('Contenido')->render() ?>
                        <br>
                        <small>Placeholders (Ticket)</small>
                    </th>
                    <td>
                        <?php $this->notification_checkout_email_content->render() ?>
                    </td>
                </tr>
            </table>
            <button type="submit" class="button-primary">
                Guardar
            </button>
        </form>
        <script>
            document.getElementById('<?= $this->notification_checkout_email_sender->id ?>').addEventListener('keydown', function (event) {
                if (event.keyCode === 32 || event.key === ' ') {
                    event.preventDefault();
                    return false;
                }
            });

            document.getElementById('<?= $this->notification_checkout_email_sender->id ?>').addEventListener('paste', function (event) {
                event.preventDefault();

                let paste = (event.clipboardData || window.clipboardData).getData('text');
                paste = paste.replace(/\s/g, '');

                this.value = paste;
            });
        </script>
        <?php
    }

    private function showMessage()
    {
        MessageAlert::getInstance(self::class)->render();
    }

    public static function writeMessage(string $message, MessageLevel $level = MessageLevel::INFO, int $expiration_seconds = 30)
    {
        (new MessageTemporal())->writeTemporalMessage(
            $message,
            self::class,
            $level,
            $expiration_seconds
        );
    }
}