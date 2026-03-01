<?php
namespace CentralBooking\Admin\Form;

use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;
use CentralBooking\Webhook\WebhookStatus;
use CentralBooking\Webhook\WebhookTopic;

final class FormWebhook implements DisplayerInterface
{
    public function render()
    {
        $webhook = $this->loadData();

        $name_input = git_input_field(['name' => 'name', 'type' => 'text', 'required' => true]);
        $topic_select = git_select_field(['name' => 'topic', 'type' => 'text', 'required' => true]);
        $status_select = git_select_field(['name' => 'status', 'type' => 'text', 'required' => true]);
        $delivery_url_input = git_input_field(['name' => 'url_delivery', 'type' => 'text', 'required' => true]);

        $name_input->styles->set('width', '300px');
        $topic_select->styles->set('width', '300px');
        $status_select->styles->set('width', '300px');
        $delivery_url_input->styles->set('width', '300px');


        foreach (WebhookStatus::cases() as $status) {
            $status_select->addOption($status->label(), $status->slug());
        }

        foreach (WebhookTopic::cases() as $topic) {
            $topic_select->addOption($topic->label(), $topic->slug());
        }

        $name_input->setValue($webhook->name);
        $topic_select->setValue($webhook->topic->slug());
        $status_select->setValue($webhook->status->slug());
        $delivery_url_input->setValue($webhook->url_delivery);

        $action = add_query_arg(
            ['action' => 'git_edit_webhook'],
            admin_url('admin-ajax.php')
        );

        $this->showMessage();
        ?>
        <form action="<?= esc_url($action) ?>" method="post">
            <?php git_nonce_field() ?>
            <input type="hidden" name="id" value="<?= esc_attr($webhook->id) ?>">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row" class="titledesc">
                            <?= $name_input->getLabel('Nombre')->compact() ?>
                        </th>
                        <td>
                            <?= $name_input->compact() ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?= $status_select->getLabel('Estado')->compact() ?>
                        </th>
                        <td>
                            <?= $status_select->compact() ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?= $topic_select->getLabel('Tema')->compact() ?>
                        </th>
                        <td>
                            <?= $topic_select->compact() ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?= $delivery_url_input->getLabel('URL de entrega')->compact() ?>
                        </th>
                        <td>
                            <?= $delivery_url_input->compact() ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="submit">
                <button type="submit" class="button button-primary">Guardar</button>
            </p>
        </form>
        <?php
    }

    public function loadData()
    {
        $id = (int) ($_GET['id'] ?? '0');
        return git_webhook_get_by_id($id) ?? git_webhook_create();
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
