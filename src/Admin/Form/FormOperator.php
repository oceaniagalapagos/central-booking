<?php
namespace CentralBooking\Admin\Form;

use CentralBooking\Admin\AdminRouter;
use CentralBooking\Admin\View\TableOperators;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\Implementation\GUI\CouponSelect;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

final class FormOperator implements DisplayerInterface
{
    public function render()
    {
        $operator = $this->loadData();

        $input_phone = git_input_field([
            'name' => 'phone',
            'type' => 'text',
            'value' => $operator->getPhone(),
            'required' => true,
            'style' => 'width:100%;',
        ]);
        $input_firstname = git_input_field([
            'name' => 'firstname',
            'type' => 'text',
            'value' => $operator->getUser()->first_name,
            'required' => true,
            'style' => 'width:100%;',
        ]);
        $input_lastname = git_input_field([
            'name' => 'lastname',
            'type' => 'text',
            'value' => $operator->getUser()->last_name,
            'required' => true,
            'style' => 'width:100%;',
        ]);
        $input_brand_media = git_input_field([
            'name' => 'brand_media',
            'type' => 'text',
            'value' => $operator->getBrandMedia(),
            'required' => false,
            'style' => 'width:100%;',
        ]);
        $input_coupons_counter = git_input_field([
            'name' => 'coupons_counter[index]',
            'type' => 'number',
            'value' => $operator->getBusinessPlan()['counter'],
            'required' => true,
            'style' => 'width:100%;',
            'min' => 0,
            'max' => $operator->getBusinessPlan()['limit'],
        ]);
        $input_coupons_limit = git_input_field([
            'name' => 'coupons_counter[limit]',
            'type' => 'number',
            'value' => $operator->getBusinessPlan()['limit'],
            'required' => true,
            'style' => 'width:100%;',
            'min' => 0,
        ]);
        $select_coupon = (new CouponSelect('coupons'))->create(true);

        foreach ($operator->getCoupons() as $coupon) {
            $select_coupon->setValue($coupon->ID);
        }

        $action = add_query_arg(
            ['action' => 'git_edit_operator'],
            admin_url('admin-ajax.php')
        );

        $this->showMessage();

        ?>
        <form id="form-operator" method="post" action="<?= $action ?>">
            <?php git_nonce_field() ?>
            <input type="hidden" name="id" value="<?= esc_attr($operator->getUser()->ID) ?>">
            <table class="form-table" role="presentation" style="max-width: 500px;">
                <tr>
                    <th colspan="2">
                        <h3>| Información del operador</h3>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $input_firstname->getLabel('Nombre')->render() ?>
                    </th>
                    <td>
                        <?php $input_firstname->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $input_lastname->getLabel('Apellidos')->render() ?>
                    </th>
                    <td>
                        <?php $input_lastname->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $input_phone->getLabel('Teléfono')->render() ?>
                    </th>
                    <td>
                        <?php $input_phone->render() ?>
                    </td>
                </tr>
                <tr>
                    <th colspan="2">
                        <h3>| Plan de cupones</h3>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $select_coupon->getLabel('Seleccionar cupones')->render() ?>
                    </th>
                    <td>
                        <?php $select_coupon->render() ?>
                        <?php $select_coupon->getOptionsContainer()->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $input_coupons_counter->getLabel('Contador de cupones')->render() ?>
                    </th>
                    <td>
                        <?php $input_coupons_counter->render() ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $input_coupons_limit->getLabel('Límite de cupones')->render() ?>
                    </th>
                    <td>
                        <?php $input_coupons_limit->render() ?>
                    </td>
                </tr>
                <tr>
                    <th colspan="2">
                        <h3>| Marketing</h3>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <?php $input_brand_media->getLabel('Medio de marca')->render() ?>
                    </th>
                    <td>
                        <?php $input_brand_media->render() ?>
                    </td>
                </tr>
            </table>
            <button class="button button-primary" type="submit">Guardar operador</button>
        </form>
        <script>
            jQuery(document).ready(function ($) {
                $('#<?= $input_coupons_limit->id ?>').on('input', function (e) {
                    const limit = parseInt($('#<?= $input_coupons_limit->id ?>').val());
                    $('#<?= $input_coupons_counter->id ?>').attr('max', limit);
                });
            });
        </script>
        <?php
    }

    private function loadData()
    {
        $id = (int) ($_GET['id'] ?? -1);
        $operator = git_operator_by_id((int) $id);

        if ($operator === null) {

            $redirect = AdminRouter::get_url_for_class(TableOperators::class);
            wp_safe_redirect($redirect);
            exit;

        }

        return $operator;
    }

    private function showMessage()
    {
        MessageAlert::getInstance(FormOperator::class)->render();
    }

    public static function writeMessage(string $message, MessageLevel $level = MessageLevel::INFO, int $expiration_seconds = 30)
    {
        (new MessageTemporal)->writeTemporalMessage(
            $message,
            FormOperator::class,
            $level,
            $expiration_seconds
        );
    }
}
