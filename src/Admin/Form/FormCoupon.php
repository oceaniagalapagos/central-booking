<?php
namespace CentralBooking\Admin\Form;

use CentralBooking\Admin\AdminRouter;
use CentralBooking\Admin\View\TableCoupons;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

final class FormCoupon implements DisplayerInterface
{
    public function render()
    {
        $coupon = $this->loadData();

        $logo_sale_input = git_input_field([
            'name' => 'brand_media',
            'type' => 'url',
            'value' => git_recover_url_brand_media_from_coupon($coupon),
            'required' => true,
        ]);

        $action = add_query_arg(
            ['action' => 'git_edit_coupon',],
            admin_url('admin-ajax.php')
        );

        $this->showMessage();

        ?>
        <form method="post" action="<?= esc_url($action) ?>">
            <?php git_nonce_field(); ?>
            <?php git_referer_field(); ?>
            <input type="hidden" name="id" value="<?= esc_attr($coupon->ID) ?>">
            <table class="form-table" role="presentation" style="max-width: 500px;">
                <tbody>
                    <tr class="form-field">
                        <th scope="row">
                            <?= $logo_sale_input->getLabel('Logo de la venta')->compact() ?>
                        </th>
                        <td>
                            <?= $logo_sale_input->compact() ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <button type="submit" class="button button-primary">Guardar</button>
        </form>
        <?php
    }

    public function loadData()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $coupon = git_coupon_by_id($id);
        if ($coupon === null) {
            $redirect = AdminRouter::get_url_for_class(TableCoupons::class);
            wp_safe_redirect($redirect);
            exit;
        }
        return $coupon;
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
