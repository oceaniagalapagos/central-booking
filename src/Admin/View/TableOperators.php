<?php
namespace CentralBooking\Admin\View;

use CentralBooking\Admin\AdminRouter;
use CentralBooking\Admin\Form\FormOperator;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

final class TableOperators implements DisplayerInterface
{
    public function render()
    {
        $this->showMessage();
        ?>
        <div style="overflow-x: auto; max-width: 800px;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col">Nombre</th>
                        <th scope="col">Teléfono</th>
                        <th scope="col">Usuario</th>
                        <th scope="col">Cupones usados</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (git_operators() as $operator): ?>
                        <tr>
                            <td style="padding-bottom: 0;">
                                <strong>
                                    <a
                                        href="<?= esc_url(AdminRouter::get_url_for_class(FormOperator::class, ['id' => $operator->getUser()->ID])) ?>">
                                        <?= esc_html($operator->getUser()->first_name . ' ' . $operator->getUser()->last_name) ?>
                                    </a>
                                </strong>
                            </td>
                            <td style="padding-bottom: 0;">
                                <?= esc_html($operator->getPhone()) ?? '—' ?>
                            </td>
                            <td style="padding-bottom: 0;">
                                <?= esc_html($operator->getUser()->user_login) ?>
                            </td>
                            <td style="padding-bottom: 0;">
                                <?= esc_html($operator->getBusinessPlan()['counter']) ?>
                                de
                                <?= esc_html($operator->getBusinessPlan()['limit']) ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4" style="padding-top: 0;">
                                <div class="row-actions visible">
                                    <span class="edit">
                                        <a href="#transport-container-<?= $operator->getUser()->ID ?>" class="git-row-action-link"
                                            data-route="<?= esc_attr($operator->getUser()->ID) ?>">
                                            Transportes (<?= count($operator->getTransports()) ?>)
                                        </a>
                                    </span>
                                    <span>|</span>
                                    <span class="edit">
                                        <a href="#coupon-container-<?= $operator->getUser()->ID ?>" class="git-row-action-link"
                                            data-route="<?= esc_attr($operator->getUser()->ID) ?>">
                                            Cupones (<?= count($operator->getCoupons()) ?>)
                                        </a>
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <tr id="actions-container-<?= $operator->getUser()->ID ?>" class="git-row-actions">
                            <td colspan="4">
                                <div id="transport-container-<?= $operator->getUser()->ID ?>" class="git-item-container hidden"
                                    data-parent="#actions-container-<?= $operator->getUser()->ID ?>">
                                    <?php foreach ($operator->getTransports() as $transport): ?>
                                        <div class="git-item">
                                            <?= esc_html($transport->nicename) ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div id="coupon-container-<?= $operator->getUser()->ID ?>" class="git-item-container hidden"
                                    data-parent="#actions-container-<?= $operator->getUser()->ID ?>">
                                    <?php foreach ($operator->getCoupons() as $coupon): ?>
                                        <div class="git-item">
                                            <?= esc_html($coupon->post_title) ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    private function showMessage()
    {
        MessageAlert::getInstance(TableOperators::class)->render();
    }

    public static function writeMessage(string $message, MessageLevel $level = MessageLevel::INFO, int $expiration_seconds = 30)
    {
        (new MessageTemporal)->writeTemporalMessage(
            $message,
            TableOperators::class,
            $level,
            $expiration_seconds
        );
    }
}