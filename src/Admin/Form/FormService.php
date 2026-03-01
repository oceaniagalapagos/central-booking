<?php
namespace CentralBooking\Admin\Form;

use CentralBooking\Admin\AdminRouter;
use CentralBooking\Admin\View\TableServices;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

final class FormService implements DisplayerInterface
{
    public const NONCE_ACTION = 'git_edit_service_action';

    public function render()
    {
        $service = $this->loadData();

        $input_icon = new InputComponent('icon', 'url');
        $input_name = new InputComponent('name', 'text');
        $input_price = new InputComponent('price', 'number');
        $select_transport = git_transport_select_field('transport_ids', true);

        $input_name->setRequired(true);
        $input_price->setRequired(true);
        $input_icon->setRequired(true);

        $input_name->setValue($service->name);
        $input_icon->setValue($service->icon);
        $input_price->setValue($service->price);

        foreach ($service->getTransports() as $transport) {
            $select_transport->setValue($transport->id);
        }

        $urlAction = add_query_arg(
            ['action' => 'git_edit_service'],
            admin_url('admin-ajax.php')
        );

        $this->showMessage();
        ?>
        <form id="form-service" method="post" action="<?= $urlAction ?>">
            <input type="hidden" name="id" value="<?= esc_attr($service->id) ?>">
            <?php git_nonce_field() ?>
            <table class="form-table" role="presentation" style="max-width: 500px;">
                <tr>
                    <th scope="row">
                        <?php $input_name->getLabel('Nombre')->render(); ?>
                    </th>
                    <td>
                        <?php $input_name->render(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $input_price->getLabel('Precio')->render(); ?>
                    </th>
                    <td>
                        <?php $input_price->render(); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $input_icon->getLabel('Icono')->render(); ?>
                    </th>
                    <td>
                        <?php $input_icon->render(); ?>
                        <p>Ingrese la direccion URL del ícono.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $select_transport->getLabel('Transportes')->render(); ?>
                    </th>
                    <td>
                        <?php
                        $select_transport->render();
                        $select_transport->getOptionsContainer()->render();
                        ?>
                    </td>
                </tr>
            </table>
            <button class="button button-primary" type="submit">Guardar</button>
        </form>
        <?php
    }

    private function loadData()
    {
        if (isset($_GET['id']) === false) {
            return git_service_create();
        }

        $id = (int) $_GET['id'];
        $service = git_service_by_id($id);

        if ($service === null) {

            TableServices::writeMessage('Se intentó cargar un servicio inexistente.', MessageLevel::WARNING);
            wp_safe_redirect(AdminRouter::get_url_for_class(TableServices::class));
            exit;

        }

        return $service;
    }

    private function showMessage()
    {
        MessageAlert::getInstance(FormService::class)->render();
    }

    public static function writeMessage(string $message, MessageLevel $level = MessageLevel::INFO, int $expiration_seconds = 30)
    {
        (new MessageTemporal)->writeTemporalMessage(
            $message,
            FormService::class,
            $level,
            $expiration_seconds
        );
    }
}
