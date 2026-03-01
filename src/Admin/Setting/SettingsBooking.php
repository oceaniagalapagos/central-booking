<?php
namespace CentralBooking\Admin\Setting;

use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

final class SettingsBooking implements DisplayerInterface
{
    public function render()
    {
        $days_without_sale_input = git_input_field([
            'name' => SettingsKeys::FORM_DAYS_WITHOUT_SALE,
            'type' => 'number',
            'value' => git_get_setting(SettingsKeys::FORM_DAYS_WITHOUT_SALE, 0),
            'required' => true,
            'min' => -365,
            'max' => 365,
        ]);
        $rpm_code_editor = git_code_editor_area_field([
            'name' => SettingsKeys::FORM_MESSAGE_RPM,
            'value' => git_get_setting(SettingsKeys::FORM_MESSAGE_RPM, ''),
            'language' => 'html',
            'rows' => 7,
        ]);
        $extra_textarea = git_code_editor_area_field([
            'name' => SettingsKeys::FORM_MESSAGE_EXTRA,
            'value' => git_get_setting(SettingsKeys::FORM_MESSAGE_EXTRA, ''),
            'language' => 'html',
            'rows' => 7,
        ]);
        $kid_code_editor = git_code_editor_area_field([
            'name' => SettingsKeys::FORM_MESSAGE_KID,
            'value' => git_get_setting(SettingsKeys::FORM_MESSAGE_KID, ''),
            'language' => 'html',
            'rows' => 7,
        ]);
        $standard_code_editor = git_code_editor_area_field([
            'name' => SettingsKeys::FORM_MESSAGE_STANDARD,
            'value' => git_get_setting(SettingsKeys::FORM_MESSAGE_STANDARD, ''),
            'language' => 'html',
            'rows' => 7,
        ]);
        $local_code_editor = git_code_editor_area_field([
            'name' => SettingsKeys::FORM_MESSAGE_LOCAL,
            'value' => git_get_setting(SettingsKeys::FORM_MESSAGE_LOCAL, ''),
            'language' => 'html',
            'rows' => 7,
        ]);
        $flexible_code_editor = git_code_editor_area_field([
            'name' => SettingsKeys::FORM_MESSAGE_FLEXIBLE,
            'value' => git_get_setting(SettingsKeys::FORM_MESSAGE_FLEXIBLE, ''),
            'language' => 'html',
            'rows' => 7,
        ]);
        $request_seats_code_editor = git_code_editor_area_field([
            'name' => SettingsKeys::FORM_MESSAGE_REQUEST_SEATS,
            'value' => git_get_setting(SettingsKeys::FORM_MESSAGE_REQUEST_SEATS, ''),
            'language' => 'html',
            'rows' => 7,
        ]);
        $terms_conditions_code_editor = git_code_editor_area_field([
            'name' => SettingsKeys::FORM_MESSAGE_TERMS_CONDITIONS,
            'value' => git_get_setting(SettingsKeys::FORM_MESSAGE_TERMS_CONDITIONS, ''),
            'language' => 'html',
            'rows' => 7,
        ]);

        $action = add_query_arg(
            ['action' => 'git_settings_booking'],
            admin_url('admin-ajax.php')
        );

        $this->showMessage();

        ?>
        <form id="git-settings-form" action="<?= esc_url($action) ?>" method="post">
            <?php git_nonce_field() ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th colspan="2">
                        <h2>| Configuración de la reserva</h2>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <?= $days_without_sale_input->getLabel('Días sin venta')->compact() ?>
                    </th>
                    <td>
                        <?= $days_without_sale_input->compact() ?>
                        <p class="description">
                            Fecha de viaje = Fecha actual + Días sin venta.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th colspan="2">
                        <h2>| Sección Regular</h2>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $standard_code_editor->getLabel('Mensaje para los clientes regular')->render() ?>
                    </th>
                    <td>
                        <?php $standard_code_editor->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $extra_textarea->getLabel('Mensaje sobre carga extra')->render() ?>
                    </th>
                    <td>
                        <?php $extra_textarea->render() ?>
                    </td>
                </tr>
                <tr>
                    <th colspan="2">
                        <h2>| Sección Preferente</h2>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $local_code_editor->getLabel('Mensaje para locales')->render() ?>
                    </th>
                    <td>
                        <?php $local_code_editor->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $rpm_code_editor->getLabel('Mensaje para los clientes RPM')->render() ?>
                    </th>
                    <td>
                        <?php $rpm_code_editor->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $kid_code_editor->getLabel('Mensaje para los clientes de edad preferente')->render() ?>
                    </th>
                    <td>
                        <?php $kid_code_editor->render() ?>
                    </td>
                </tr>
                <tr>
                    <th colspan="2">
                        <h2>| Sección Extra</h2>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $flexible_code_editor->getLabel('Mensaje sobre flexibilidad')->render() ?>
                    </th>
                    <td>
                        <?php $flexible_code_editor->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $terms_conditions_code_editor->getLabel('Términos y condiciones')->render() ?>
                    </th>
                    <td>
                        <?php $terms_conditions_code_editor->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $request_seats_code_editor->getLabel('Solicitud de más asientos')->render() ?>
                    </th>
                    <td>
                        <?php $request_seats_code_editor->render() ?>
                    </td>
                </tr>
            </table>
            <button type="submit" class="button-primary" id="git-save-button">
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