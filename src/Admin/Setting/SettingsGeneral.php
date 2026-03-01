<?php
namespace CentralBooking\Admin\Setting;

use CentralBooking\Admin\AdminRouter;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\Implementation\GUI\PageSelect;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

final class SettingsGeneral implements DisplayerInterface
{
    public const ACTION_NONCE = 'git_edit_settings_general';

    public function render()
    {
        $this->showMessage();
        ?>
        <div class="wrap">
            <div class="wrap">
                <button type="button" class="button" id="import_data_button">Importar datos</button>
                <button type="button" class="button" id="export_data_button">Exportar datos</button>
                <?php
                $this->import_data_form();
                $this->export_data_form();
                ?>
            </div>
            <hr>
            <h3>| Seguridad</h3>
            <?php $this->formSecretKey(); ?>
            <h3>| General</h3>
            <?php $this->formGeneral(); ?>
        </div>
        <?php
    }

    private function formSecretKey()
    {
        ?>
        <?php git_nonce_field(); ?>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="secret_key">Llave secreta</label>
                </th>
                <td>
                    <p class="description">La llave secreta se utilizará en algunas funcionalidades sensibles del
                        sistema. <a href="<?= esc_url(AdminRouter::get_url_for_class(SettingsSecretKey::class)) ?>">Ver
                            documentación de la clave secreta.</a></p>
                </td>
        </table>
        <?php
    }

    private function formGeneral()
    {
        $page_select = (new PageSelect(SettingsKeys::GENERAL_PROFILE_PAGE))->create();
        $page_select->setRequired(true);
        $page_select->setValue(git_get_setting(SettingsKeys::GENERAL_PROFILE_PAGE, 0));
        $general_file_size = git_input_field([
            'name' => SettingsKeys::GENERAL_FILE_SIZE,
            'type' => 'number',
            'value' => git_get_setting(SettingsKeys::GENERAL_FILE_SIZE, 1),
            'min' => 1,
            'max' => 1024,
            'required' => true,
        ]);
        $general_file_extension = git_input_field([
            'name' => SettingsKeys::GENERAL_FILE_EXTENSION,
            'type' => 'text',
            'value' => join(', ', git_get_setting(SettingsKeys::GENERAL_FILE_EXTENSION, ['.jpg', '.png', '.pdf'])),
            'required' => true,
        ]);
        $action = esc_url(add_query_arg(
            ['action' => 'git_settings_general'],
            admin_url('admin-ajax.php')
        ));
        ?>
        <form action="<?= $action ?>" method="post">
            <?php git_nonce_field(); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <?php $page_select->getLabel('Pagina de perfil')->render() ?>
                    </th>
                    <td>
                        <?php $page_select->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $general_file_size->getLabel('Tamaño máximo del archivo (MB)')->render() ?>
                    </th>
                    <td>
                        <?php $general_file_size->render() ?>
                        <p class="description">Ingrese el tamaño máximo del archivo en megabytes (MB). Valor entre 1 y 1024 MB.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $general_file_extension->getLabel('Extensiones de archivo permitidas')->render() ?>
                    </th>
                    <td>
                        <?php $general_file_extension->render() ?>
                        <p class="description">
                            Ingrese las extensiones de archivo permitidas, separadas por comas.<br>
                            <strong>Ejemplo:</strong> jpg, png, pdf, doc, zip<br>
                            <em>Nota: Los puntos se agregarán automáticamente si no los incluye.</em>
                        </p>
                        <div id="extensions_preview" class="extensions-preview" style="margin-top: 10px;"></div>
                        <div id="extensions_error" class="error-message"
                            style="display: none; color: #dc3232; font-weight: bold;">
                        </div>
                    </td>
                </tr>
            </table>
            <button type="submit" class="button-primary">Guardar</button>
        </form>
        <?php
    }

    private function import_data_form()
    {
        $file_input = new InputComponent('git_data', 'file');
        $nonce_input = new InputComponent('nonce', 'hidden');
        $nonce_input->setValue(wp_create_nonce('git-import-data'));
        $file_input->attributes->set('accept', '.json');

        $action = add_query_arg(
            ['action' => 'git_import_data'],
            admin_url('admin-ajax.php')
        );
        ?>
        <div class="git-import-data">
            <form method="post" enctype="multipart/form-data" class="git-import-data-form" action="<?= esc_url($action) ?>">
                <h3>Sube los datos que quieres cargar al sistema GIT</h3>
                <?php wp_nonce_field(self::ACTION_NONCE); ?>
                <?php $nonce_input->render(); ?>
                <?php $file_input->render(); ?>
                <button type="submit" class="button button-primary" disabled>Subir</button>
            </form>
        </div>
        <?php
    }

    private function export_data_form()
    {
        $settings_input = new InputComponent('settings_data', 'checkbox');
        $entities_input = new InputComponent('entities_data', 'checkbox');
        $products_input = new InputComponent('products_data', 'checkbox');

        $settings_input->class_list->add('git-export-settings');
        $entities_input->class_list->add('git-export-settings');
        $products_input->class_list->add('git-export-settings');

        $action = add_query_arg(
            ['action' => 'git_export_data'],
            admin_url('admin-ajax.php')
        );

        ?>
        <div class="git-export-data">
            <form method="post" class="git-import-data-form" action="<?= esc_url($action) ?>">
                <?php wp_nonce_field(self::ACTION_NONCE); ?>
                <h3>¿Qué deseas exportar?</h3>
                <div class="git-export-options">
                    <p>
                        <?php
                        $settings_input->render();
                        $settings_input->getLabel('Configuraciones')->render();
                        ?>
                    </p>
                    <p>
                        <?php
                        $entities_input->render();
                        $entities_input->getLabel('Entidades de datos')->render();
                        ?>
                    </p>
                    <p>
                        <?php
                        $products_input->render();
                        $products_input->getLabel('Productos')->render();
                        ?>
                    </p>
                    <p class="submit inline-edit-save" style="justify-content: center;">
                        <button type="submit" class="button button-primary">Descargar</button>
                    </p>
                </div>
            </form>
        </div>
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