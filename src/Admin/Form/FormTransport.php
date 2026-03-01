<?php
namespace CentralBooking\Admin\Form;

use CentralBooking\Admin\AdminRouter;
use CentralBooking\Admin\View\TableTransports;
use CentralBooking\Data\Constants\TransportCustomeFieldConstants;
use CentralBooking\Data\Transport;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\GUI\SelectComponent;
use CentralBooking\GUI\TextareaComponent;
use CentralBooking\Implementation\GUI\TypeOperationSelect;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

final class FormTransport implements DisplayerInterface
{
    public const NONCE_ACTION = 'edit_transport';

    public function render()
    {
        $transport = $this->loadData();

        $input_photo_url = git_input_field([
            'name' => 'photo_url',
            'type' => 'url',
            'value' => $transport->getUrlPhoto()
        ]);
        $select_type = (new TypeOperationSelect('type'))->create();
        $input_nicename = git_input_field([
            'name' => 'nicename',
            'type' => 'text',
            'required' => true,
            'value' => $transport->nicename
        ]);
        $input_capacity = git_input_field([
            'name' => 'capacity',
            'type' => 'number',
            'required' => true,
            'value' => $transport->getCapacity()
        ]);
        $input_code = git_input_field([
            'name' => 'code',
            'type' => 'text',
            'required' => true,
            'value' => $transport->code
        ]);
        $select_operator = git_operator_select_field('operator_id');
        $select_routes = git_route_select_field('routes_id', true);
        $select_services = git_service_select_field('services_id', true);
        $custom_field_content = new TextareaComponent('custom_field[content]');
        $custom_field_field = new SelectComponent('custom_field[field]');

        $days = [
            'monday' => 'Lunes',
            'tuesday' => 'Martes',
            'wednesday' => 'Miércoles',
            'thursday' => 'Jueves',
            'friday' => 'Viernes',
            'saturday' => 'Sábado',
            'sunday' => 'Domingo',
        ];

        $select_type->setRequired(true);
        $select_operator->setRequired(true);

        $custom_field_content->attributes->set('rows', '4');
        $custom_field_content->attributes->set('cols', '60');
        $custom_field_field->styles->set('margin-bottom', '10px');

        foreach (TransportCustomeFieldConstants::cases() as $field) {
            $custom_field_field->addOption($field->label(), $field->slug());
        }

        $custom_field_field->setValue($transport->getCustomField()['field']->slug());
        $custom_field_content->setValue($transport->getCustomField()['content']);
        $select_operator->setValue($transport->getOperator()->getUser()->ID);
        $select_type->setValue($transport->type->slug());
        foreach ($transport->getRoutes() as $route)
            $select_routes->setValue($route->id);
        foreach ($transport->getServices() as $service)
            $select_services->setValue($service->id);

        $action = add_query_arg(
            ['action' => 'git_edit_transport'],
            admin_url('admin-ajax.php')
        );

        $this->showMessage();
        ?>
        <div id="template-form-crew-member" style="display: none;">
            <?php $this->create_form_crew_member('{{ID}}') ?>
        </div>
        <div id="template-form-alias" style="display: none;">
            <?php $this->createFormAlias('{{ID}}') ?>
        </div>
        <form method="post" action="<?= esc_attr($action) ?>">
            <?php wp_nonce_field(self::NONCE_ACTION) ?>
            <input type="hidden" name="id" value="<?= esc_attr($transport->id) ?>">
            <table class="form-table" role="presentation" style="max-width: 700px;">
                <tr>
                    <th scope="row">
                        <?php $input_nicename->getLabel('Nombre')->render() ?>
                    </th>
                    <td>
                        <?php $input_nicename->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $input_capacity->getLabel('Capacidad')->render() ?>
                    </th>
                    <td>
                        <?php $input_capacity->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $select_operator->getLabel('Operador')->render() ?>
                    </th>
                    <td>
                        <?php $select_operator->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $select_type->getLabel('Tipo de transporte')->render() ?>
                    </th>
                    <td>
                        <?php $select_type->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $input_code->getLabel('Código')->render() ?>
                    </th>
                    <td>
                        <?php $input_code->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $select_routes->getLabel('Rutas')->render() ?>
                    </th>
                    <td>
                        <?php
                        $select_routes->render();
                        $select_routes->getOptionsContainer()->render();
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $select_services->getLabel('Servicios')->render() ?>
                    </th>
                    <td>
                        <?php
                        $select_services->render();
                        $select_services->getOptionsContainer()->render();
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label>Tripulación</label>
                    </th>
                    <td>
                        <div id="container-crew-member-fields"></div>
                        <button onclick="addCrewMemberField()" class="button button-primary" type="button">
                            <i class="bi bi-plus"></i> Añadir tripulante
                        </button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label>Alias</label>
                    </th>
                    <td>
                        <div id="container-alias-fields">
                            <?php
                            foreach ($transport->getAlias() as $key => $al)
                                $this->createFormAlias("alias_index_{$key}", $al);
                            ?>
                        </div>
                        <button onclick="addAliasField()" class="button button-primary" type="button">
                            <i class="bi bi-plus"></i> Añadir alias
                        </button>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label>Disponibilidad diaria</label>
                    </th>
                    <td>
                        <ul>
                            <?php foreach ($days as $day_value => $day_label): ?>
                                <li>
                                    <input id="check_day_<?= $day_value ?>" type="checkbox" name="availability_days[]"
                                        value="<?= esc_attr($day_value) ?>" <?= in_array($day_value, $transport->getWorkingDays()) ? 'checked' : '' ?>>
                                    <label for="check_day_<?= $day_value ?>">
                                        <?= esc_html($day_label) ?>
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?= $input_photo_url->getLabel('URL de la foto del transporte')->render() ?>
                    </th>
                    <td>
                        <?= $input_photo_url->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?= $custom_field_content->getLabel('Campo Personalizado')->render() ?>
                    </th>
                    <td>
                        <?= $custom_field_field->render() ?>
                        <?= $custom_field_content->render() ?>
                    </td>
                </tr>
            </table>
            <button type="submit" class="button-primary">Guardar Transporte</button>
        </form>
        <?php

        $this->loadScripts($transport);
    }

    private function loadScripts(Transport $transport)
    {
        wp_enqueue_script(
            'central-booking-admin-transport-form',
            CENTRAL_BOOKING_URL . 'assets/js/admin/transport-form.js',
            ['jquery'],
            time(),
            true
        );
        wp_localize_script(
            'central-booking-admin-transport-form',
            'transportFormData',
            [
                'crewMembers' => array_map(
                    fn($member) => [
                        'role' => $member['role'],
                        'name' => $member['name'],
                        'contact' => $member['contact'],
                        'license' => $member['license'],
                    ],
                    $transport->getCrew()
                ),
            ]
        );
    }

    private function createFormAlias(string $id, string $value = '')
    {
        $target = esc_attr($id);
        ?>
        <div id="<?= $target ?>" class="alias_input_form" style="margin-bottom: 10px;">
            <input value="<?= esc_attr($value) ?>" type="text" name="alias[]">
            <button type="button" class="button-secondary" onclick="removeAliasField(this)"
                data-target="<?= $target ?>">Eliminar alias</button>
        </div>
        <?php
    }

    private function create_form_crew_member(
        string $id,
        array $member = [
            'role' => '',
            'name' => '',
            'contact' => '',
            'license' => '',
        ]
    ) {
        $id = esc_attr("crew_member_{$id}");
        ?>
        <table id="<?= $id ?>" data-index="{{INDEX}}" class="form-table crew_member_input_form" role="presentation"
            style="margin-bottom: 20px; border-bottom: 1px solid #ccc;">
            <tr>
                <td scope="row">
                    <label>Nombre <span class="required">*</span></label>
                </td>
                <td>
                    <input type="text" name="crew[{{INDEX}}][name]" value="<?= esc_attr($member['name']); ?>" required>
                </td>
            </tr>
            <tr>
                <td scope="row">
                    <label>Rol <span class="required">*</span></label>
                </td>
                <td>
                    <input type="text" name="crew[{{INDEX}}][role]" value="<?= esc_attr($member['role']); ?>" required>
                </td>
            </tr>
            <tr>
                <td scope="row">
                    <label>Contacto <span class="required">*</span></label>
                </td>
                <td>
                    <input type="text" name="crew[{{INDEX}}][contact]" value="<?= esc_attr($member['contact']); ?>" required>
                </td>
            </tr>
            <tr>
                <td scope="row">
                    <label>Licencia <span class="required">*</span></label>
                </td>
                <td>
                    <input type="text" name="crew[{{INDEX}}][license]" value="<?= esc_attr($member['license']); ?>" required>
                </td>
            </tr>
            <tr>
                <td scope="row">
                    <button type="button" data-target="<?= $id ?>" onclick="removeCrewMemberField(this)"
                        class="button button-secondary button_remove_crew_member">
                        Eliminar tripulante
                    </button>
                </td>
            </tr>
        </table>
        <?php
    }

    private function loadData()
    {
        if (isset($_GET['id']) === false) {
            return git_transport_create();
        }

        $id = (int) $_GET['id'];
        $service = git_transport_by_id($id);

        if ($service === null) {

            TableTransports::writeMessage('Se intentó cargar un transporte inexistente.', MessageLevel::WARNING);
            wp_safe_redirect(AdminRouter::get_url_for_class(TableTransports::class));
            exit;

        }

        return $service;
    }

    private function showMessage()
    {
        MessageAlert::getInstance(FormTransport::class)->render();
    }

    public static function writeMessage(string $message, MessageLevel $level = MessageLevel::SUCCESS, int $expiration_seconds = 30)
    {
        (new MessageTemporal)->writeTemporalMessage(
            $message,
            FormTransport::class,
            $level,
            $expiration_seconds
        );
    }
}
