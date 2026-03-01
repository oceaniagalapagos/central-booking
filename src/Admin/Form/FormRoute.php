<?php
namespace CentralBooking\Admin\Form;

use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\GUI\MultipleSelectComponent;
use CentralBooking\GUI\SelectComponent;
use CentralBooking\Implementation\GUI\TypeOperationSelect;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

final class FormRoute implements DisplayerInterface
{
    public const NONCE_ACTION = 'edit_route';
    private SelectComponent $select_origin;
    private SelectComponent $select_destiny;
    private SelectComponent $select_type;
    private InputComponent $input_arrival_time;
    private InputComponent $input_departure_time;
    private MultipleSelectComponent $select_transport;

    public function __construct()
    {
        $this->select_type = (new TypeOperationSelect('type'))->create();
        $this->select_origin = git_location_select_field('origin_id');
        $this->select_destiny = git_location_select_field('destiny_id');
        $this->select_transport = git_transport_select_field('transports_id', true);
        $this->input_arrival_time = git_input_field([
            'name' => 'arrival_time',
            'type' => 'time',
            'required' => true
        ]);
        $this->input_departure_time = git_input_field([
            'name' => 'departure_time',
            'type' => 'time',
            'required' => true
        ]);

        $this->select_type->setRequired(true);
        $this->select_origin->setRequired(true);
        $this->select_destiny->setRequired(true);

        $this->select_type->styles->set('width', '100%');
        $this->select_origin->styles->set('width', '100%');
        $this->select_destiny->styles->set('width', '100%');
        $this->select_transport->styles->set('width', '100%');
        $this->input_arrival_time->styles->set('width', '100%');
        $this->input_departure_time->styles->set('width', '100%');
    }

    public function render()
    {
        $route = $this->loadData();

        $this->select_type->setValue($route->type->slug());
        $this->select_origin->setValue($route->getOrigin()?->id ?? 0);
        $this->select_destiny->setValue($route->getDestiny()?->id ?? 0);
        $this->input_arrival_time->setValue(esc_attr($route->getArrivalTime()?->format() ?? ''));
        $this->input_departure_time->setValue(esc_attr($route->getDepartureTime()?->format() ?? ''));
        foreach ($route->getTransports() as $transport)
            $this->select_transport->setValue($transport->id);

        $action = add_query_arg(
            ['action' => 'git_edit_route'],
            admin_url('admin-ajax.php')
        );
        $this->showMessage();
        ?>
        <form method="post" action="<?= esc_attr($action) ?>">
            <input type="hidden" name="id" value="<?= $route->id ?>">
            <?php wp_nonce_field(self::NONCE_ACTION) ?>
            <table class="form-table" role="presentation" style="max-width: 500px;">
                <tr>
                    <th scope="row">
                        <?php $this->select_origin->getLabel('Origen')->render() ?>
                    </th>
                    <td>
                        <?php
                        $this->select_origin->render();
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->select_destiny->getLabel('Destino')->render() ?>
                    </th>
                    <td>
                        <?php
                        $this->select_destiny->render();
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->select_type->getLabel('Tipo de operación')->render() ?>
                    </th>
                    <td>
                        <?php
                        $this->select_type->render();
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_departure_time->getLabel('Hora de salida')->render() ?>
                    </th>
                    <td>
                        <?php
                        $this->input_departure_time->render();
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->input_arrival_time->getLabel('Hora de llegada')->render() ?>
                    </th>
                    <td>
                        <?php
                        $this->input_arrival_time->render();
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $this->select_transport->getLabel('Transportes')->render() ?>
                    </th>
                    <td>
                        <?php
                        $this->select_transport->render();
                        $this->select_transport->getOptionsContainer()->render();
                        ?>
                    </td>
                </tr>
            </table>
            <button type="submit" class="button button-primary">Guardar</button>
        </form>
        <?php
    }

    private function loadData()
    {
        $id = (int) ($_GET['id'] ?? 0);
        return git_route_by_id($id) ?? git_route_create();
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
