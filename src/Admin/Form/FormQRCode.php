<?php
namespace CentralBooking\Admin\Form;

use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\QR\ErrorCorrectionCode;

final class FormQRCode implements DisplayerInterface
{
    public function __construct()
    {
        $this->imports();
    }

    private function imports()
    {
        wp_enqueue_script(
            'central-booking-qr-generator',
            CENTRAL_BOOKING_URL . '/assets/js/admin/qr-generator.js',
            ['jquery'],
            time(),
            true
        );
        wp_enqueue_style(
            'central-booking-qr-generator-style',
            CENTRAL_BOOKING_URL . '/assets/css/admin/qr-generator.css'
        );
        wp_localize_script(
            'central-booking-qr-generator',
            'CentralBookingQRGenerator',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'action' => 'git_qr_generator'
            ]
        );
    }

    public function render()
    {
        ob_start();
        ?>
        <form method="get" id="qr-generator-form" style="max-width: 750px;">
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <td colspan="2">
                            <h3>Formato</h3>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="type_qr">Tipo de información <span class="required">*</span></label>
                        </td>
                        <td>
                            <select id="type_qr" name="type" style="width: 100%;" required>
                                <option value="url" data-target-panel="qr-panel-url">URL</option>
                                <option value="email" data-target-panel="qr-panel-email">Email</option>
                                <option value="phone" data-target-panel="qr-panel-phone">Teléfono</option>
                                <option value="wifi" data-target-panel="qr-panel-wifi">WiFi</option>
                                <option value="whatsapp" data-target-panel="qr-panel-whatsapp">WhatsApp</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="size_qr">Tamaño del QR <span class="required">*</span></label>
                        </td>
                        <td>
                            <input name="size" id="size_qr" value="200" min="100" max="1000" type="number" style="width: 100%;"
                                required>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="margin_qr">Margen del QR <span class="required">*</span></label>
                        </td>
                        <td>
                            <input name="margin" id="margin_qr" value="10" min="0" max="50" type="number" style="width: 100%;"
                                required>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="color_qr">Color del QR <span class="required">*</span></label>
                        </td>
                        <td>
                            <input name="color" id="color_qr" value="#000000" type="color" style="width: 100%;" required>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="background_color_qr">Color de fondo del QR <span class="required">*</span></label>
                        </td>
                        <td>
                            <input name="bgcolor" id="background_color_qr" value="#FFFFFF" type="color" style="width: 100%;"
                                required>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="ecc_qr">Código de corrección de errores <span class="required">*</span></label>
                        </td>
                        <td>
                            <select name="ecc" id="ecc_qr" style="width: 100%;" required>
                                <?php foreach (ErrorCorrectionCode::cases() as $ecc): ?>
                                    <option value="<?= $ecc->slug() ?>"><?= $ecc->label() ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <h3>Información</h3>
                        </td>
                    </tr>
                </tbody>

                <tbody class="qr-panel" id="qr-panel-url" style="display: none;">
                    <tr>
                        <td>
                            <label for="url_qr">URL <span class="required">*</span></label>
                        </td>
                        <td>
                            <input class="form-control" name="url" id="url_qr" type="url" style="width: 100%;"
                                data-required="true">
                        </td>
                    </tr>
                </tbody>

                <tbody class="qr-panel" id="qr-panel-email" style="display: none;">
                    <tr>
                        <td>
                            <label for="email_qr">Email <span class="required">*</span></label>
                        </td>
                        <td>
                            <input class="form-control" name="email" id="email_qr" type="email" style="width: 100%;"
                                data-required="true">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="email_subject_qr">Asunto del Email</label>
                        </td>
                        <td>
                            <input class="form-control" name="email_subject" id="email_subject_qr" type="text"
                                style="width: 100%;" data-required="false">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="email_content_qr">Contenido del Email</label>
                        </td>
                        <td>
                            <textarea class="form-control" name="email_message" id="email_content_qr" style="width: 100%;"
                                rows="5" data-required="false"></textarea>
                        </td>
                    </tr>
                </tbody>

                <tbody class="qr-panel" id="qr-panel-phone" style="display: none;">
                    <tr>
                        <td>
                            <label for="phone_qr">Teléfono <span class="required">*</span></label>
                        </td>
                        <td>
                            <input class="form-control" name="phone" id="phone_qr" type="tel" style="width: 100%;"
                                data-required="true">
                            <p class="description">
                                Incluye el código de país. Ejemplo: +34123456789
                            </p>
                        </td>
                    </tr>
                </tbody>

                <tbody class="qr-panel" id="qr-panel-wifi" style="display: none;">
                    <tr>
                        <td>
                            <label for="ssid_qr">Red <span class="required">*</span></label>
                        </td>
                        <td>
                            <input class="form-control" name="ssid" id="ssid_qr" type="text" style="width: 100%;"
                                data-required="true">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="password_qr">Contraseña <span class="required">*</span></label>
                        </td>
                        <td>
                            <input class="form-control" name="password" id="password_qr" type="password" style="width: 100%;"
                                data-required="true">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="encryption_qr">Seguridad <span class="required">*</span></label>
                        </td>
                        <td>
                            <select class="form-control" name="encryption" id="encryption_qr" style="width: 100%;"
                                data-required="true">
                                <option value="WPA">WPA/WPA2</option>
                                <option value="WEP">WEP</option>
                                <option value="nopass">Sin contraseña</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="hidden_qr">Red oculta</label>
                        </td>
                        <td>
                            <input class="form-check-input" type="checkbox" name="hidden" id="hidden_qr" value="1">
                        </td>
                    </tr>
                </tbody>

                <tbody class="qr-panel" id="qr-panel-whatsapp" style="display: none;">
                    <tr>
                        <td>
                            <label for="whatsapp_number_qr">Teléfono <span class="required">*</span></label>
                        </td>
                        <td>
                            <input class="form-control" name="whatsapp_number" id="whatsapp_number_qr" type="text"
                                style="width: 100%;" data-required="true">
                            <p class="description">
                                Incluye el código de país, pero sin el símbolo "+" ni espacios. Ejemplo: 34123456789
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="whatsapp_message_qr">Mensaje</label>
                        </td>
                        <td>
                            <textarea class="form-control" name="whatsapp_message" id="whatsapp_message_qr" style="width: 100%;"
                                rows="5" data-required="false"></textarea>
                        </td>
                    </tr>
                </tbody>

            </table>
            <button type="submit" class="button button-primary" id="generate-qr-button">Generar</button>
        </form>
        <div class="qr-container" id="qr-container" style="display: none;">
        </div>
        <?php
        echo ob_get_clean();
    }
}