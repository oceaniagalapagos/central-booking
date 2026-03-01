<?php
namespace CentralBooking\Admin\Form;

use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

final class FormZone implements DisplayerInterface
{
    public function render()
    {
        $zone = $this->loadData();

        $input_name = git_input_field([
            'name' => 'name',
            'type' => 'text',
            'required' => true,
            'value' => $zone->name,
        ]);

        $action = add_query_arg(
            ['action' => 'git_edit_zone'],
            admin_url('admin-ajax.php')
        );

        $this->showMessage();

        ?>
        <form method="post" action="<?= $action ?>">
            <?php git_nonce_field() ?>
            <input type="hidden" name="id" value="<?= esc_attr($zone->id) ?>">
            <table class="form-table" role="presentation" style="max-width: 500px;">
                <tr>
                    <th scope="row">
                        <?php $input_name->getLabel('Nombre')->render() ?>
                    </th>
                    <td>
                        <?php $input_name->render() ?>
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
        return git_zone_by_id($id) ?? git_zone_create();
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
