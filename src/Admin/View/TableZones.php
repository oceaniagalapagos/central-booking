<?php
namespace CentralBooking\Admin\View;

use CentralBooking\Admin\AdminRouter;
use CentralBooking\Admin\Form\FormZone;
use CentralBooking\Data\Services\ZoneService;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

final class TableZones implements DisplayerInterface
{
    private ZoneService $zoneService;

    public function __construct()
    {
        $this->zoneService = new ZoneService();
    }

    public function render()
    {
        $this->showMessage();
        ?>
        <div style="max-width: 200px;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 200px;" scope="col">Nombre</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->getZones() as $zone): ?>
                        <tr>
                            <td>
                                <a href="<?= AdminRouter::get_url_for_class(FormZone::class, ['id' => $zone->id]) ?>">
                                    <strong>
                                        <?= esc_html($zone->name) ?>
                                    </strong>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    private function getZones()
    {
        return git_zones();
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