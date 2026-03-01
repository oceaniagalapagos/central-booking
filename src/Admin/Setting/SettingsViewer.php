<?php
namespace CentralBooking\Admin\Setting;

use CentralBooking\Data\Passenger;
use CentralBooking\Data\Ticket;
use CentralBooking\GUI\AccordionComponent;
use CentralBooking\GUI\CodeEditorComponent;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\GUI\SelectComponent;
use CentralBooking\Implementation\GUI\PageSelect;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;
use CentralBooking\Placeholders\PlaceholderEnginePassenger;
use CentralBooking\Placeholders\PlaceholderEngineTicket;

final class SettingsViewer implements DisplayerInterface
{
    public const ACTION_NONCE = 'git_settings_nonce';
    private SelectComponent $page_viewer;
    private CodeEditorComponent $viewer_css;
    private CodeEditorComponent $viewer_js;
    private CodeEditorComponent $ticket_viewer_html;
    private CodeEditorComponent $passenger_viewer_html;
    private InputComponent $default_media;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $this->page_viewer = (new PageSelect('ticket_viewer'))->create();
        $this->viewer_js = new CodeEditorComponent('ticket_viewer_js');
        $this->viewer_css = new CodeEditorComponent('ticket_viewer_css');
        $this->ticket_viewer_html = new CodeEditorComponent('ticket_viewer_html');
        $this->passenger_viewer_html = new CodeEditorComponent('ticket_viewer_passenger_html');
        $this->default_media = new InputComponent('ticket_viewer_default_media', 'url');

        foreach ([
            $this->viewer_js,
            $this->viewer_css,
            $this->ticket_viewer_html,
            $this->passenger_viewer_html,
            $this->default_media,
        ] as $code_editor) {
            $code_editor->attributes->set('rows', 7);
            $code_editor->styles->set('width', '100%');
        }

        $this->page_viewer->setValue(git_get_setting(SettingsKeys::TICKET_VIEWER, ''));
        $this->viewer_js->setValue(git_get_setting(SettingsKeys::TICKET_VIEWER_JS, ''));
        $this->viewer_css->setValue(git_get_setting(SettingsKeys::TICKET_VIEWER_CSS, ''));
        $this->default_media->setValue(git_get_setting(SettingsKeys::TICKET_VIEWER_DEFAULT_MEDIA, ''));
        $this->ticket_viewer_html->setValue(git_get_setting(SettingsKeys::TICKET_VIEWER_HTML, ''));
        $this->passenger_viewer_html->setValue(git_get_setting(SettingsKeys::TICKET_VIEWER_PASSENGER_HTML, ''));

        $this->viewer_js->set_language('js');
        $this->viewer_css->set_language('css');
        $this->ticket_viewer_html->set_language('html');
        $this->passenger_viewer_html->set_language('html');
    }

    public function render()
    {
        $accordion = new AccordionComponent();
        $accordion->addItem(
            git_string_to_component('<i class="bi bi-bookmark"></i> Placeholders (Ticket)'),
            (new PlaceholderEngineTicket(new Ticket()))->get_placeholders_info(),
        );
        $accordion->addItem(
            git_string_to_component('<i class="bi bi-bookmark"></i> Placeholders (Pasajero)'),
            (new PlaceholderEnginePassenger(new Passenger()))->get_placeholders_info(),
        );
        $accordion->styles->set('margin-top', '20px');
        $accordion->render();
        $action = add_query_arg(
            'action',
            'git_setting_ticket_viewer',
            admin_url('admin-ajax.php')
        );
        $this->showMessage();
        ?>
        <form action="<?= esc_url($action) ?>" method="post">
            <?php wp_nonce_field(self::ACTION_NONCE, 'nonce') ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <?php $this->page_viewer->getLabel('Página de visor')->render(); ?>
                    </th>
                    <td>
                        <?php $this->page_viewer->render(); ?>
                        <p class="description">
                            Seleccione la página donde se redirigiran los QR de los tickets generados.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->ticket_viewer_html->getLabel('Visor de tickets (html)')->render() ?>
                    </th>
                    <td>
                        <?php $this->ticket_viewer_html->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->viewer_js->getLabel('Visor de tickets (js)')->render() ?>
                    </th>
                    <td>
                        <?php $this->viewer_js->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->viewer_css->getLabel('Visor de tickets (css)')->render() ?>
                    </th>
                    <td>
                        <?php $this->viewer_css->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->passenger_viewer_html->getLabel('Visor de pasajeros (html)')->render() ?>
                    </th>
                    <td>
                        <?php $this->passenger_viewer_html->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->default_media->getLabel('Medio por defecto')->render() ?>
                    </th>
                    <td>
                        <?php $this->default_media->render() ?>
                    </td>
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
        (new MessageTemporal())->writeTemporalMessage(
            $message,
            self::class,
            $level,
            $expiration_seconds
        );
    }
}