<?php
namespace CentralBooking\Admin\Form;

use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\Implementation\GUI\ZoneSelect;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

final class FormLocation implements DisplayerInterface
{
    public function render()
    {
        $location = $this->loadData();

        $input_name = git_input_field([
            'name' => 'name',
            'type' => 'text',
            'value' => $location->name,
            'required' => true,
            'style' => 'width:100%;',
        ]);

        $select_zone = (new ZoneSelect('zone_id'))->create();
        $select_zone->setRequired(true);
        $select_zone->styles->set('width', '100%');
        $select_zone->setValue($location->getZone()->id);

        $action = add_query_arg(
            ['action' => 'git_edit_location'],
            admin_url('admin-ajax.php')
        );

        $this->showMessage();

        ?>
        <form id="form-location" method="post" action="<?= esc_url($action) ?>">
            <?php git_nonce_field() ?>
            <?php git_referer_field() ?>
            <input type="hidden" name="id" value="<?= esc_attr($location->id) ?>">
            <table class="form-table" role="presentation" style="max-width: 500px;">
                <tr>
                    <th scope="row">
                        <?php $input_name->getLabel('Nombre')->render() ?>
                    </th>
                    <td>
                        <?php $input_name->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $select_zone->getLabel('Zona')->render() ?>
                    </th>
                    <td>
                        <?php $select_zone->render() ?>
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
        return git_location_by_id($id) ?? git_location_create();
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
