<?php
namespace CentralBooking\Profile\Forms;

use CentralBooking\Data\Constants\UserRole;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\GUI\InputFloatingLabelComponent;
use CentralBooking\GUI\SelectComponent;
use CentralBooking\Implementation\GUI\CouponSelect;

class FormInvoice implements DisplayerInterface
{
    private SelectComponent $coupon_select;
    private SelectComponent $operator_select;
    private InputComponent $operator_input;
    private SelectComponent $month_select;
    private SelectComponent $year_select;
    private InputComponent $date_start_input;
    private InputComponent $date_end_input;
    
    public function __construct()
    {
        if (git_current_user_has_role(UserRole::ADMINISTRATOR)) {
            $this->coupon_select = (new CouponSelect('coupon'))->create();
        } else {
            $this->coupon_select = (new CouponSelect('coupon', get_current_user_id()))->create();
        }
        $this->date_start_input = new InputComponent('date_start', 'date');
        $this->date_end_input = new InputComponent('date_end', 'date');
        $this->operator_select = git_operator_select_field('operator');
        $this->operator_input = new InputComponent('operator', 'hidden');
        $this->month_select = $this->create_select_month();
        $this->year_select = $this->create_select_year();

        $this->coupon_select->setValue($_GET['coupon'] ?? '');
        $this->operator_select->setValue($_GET['operator'] ?? '');
        $this->operator_input->setValue(get_current_user_id());
        $this->date_start_input->setValue($_GET['date_start'] ?? null);
        $this->date_end_input->setValue($_GET['date_end'] ?? null);
        $this->operator_select->setRequired(true);
        $this->month_select->setRequired(true);
        $this->year_select->setRequired(true);
        $this->date_start_input->setRequired(true);
        $this->date_end_input->setRequired(true);
    }

    public function render()
    {
        $date_start_floating = new InputFloatingLabelComponent($this->date_start_input, 'Fecha inicio');
        $date_end_floating = new InputFloatingLabelComponent($this->date_end_input, 'Fecha fin');
        $coupon_floating = new InputFloatingLabelComponent($this->coupon_select, 'Cupón');
        $operator_floating = new InputFloatingLabelComponent($this->operator_select, 'Operador');
        $month_floating = new InputFloatingLabelComponent($this->month_select, 'Mes de facturación');
        $year_floating = new InputFloatingLabelComponent($this->year_select, 'Año de facturación');
        $this->operator_select->setValue(get_current_user_id());
        ?>
        <form method="get" class="p-3">
            <input type="hidden" name="tab" value="sales">
            <div class="row mb-3">
                <div class="col">
                    <?php
                    if (git_current_user_has_role(UserRole::ADMINISTRATOR))
                        $operator_floating->render();
                    else
                        $this->operator_input->render();
                    ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <?= $coupon_floating->compact() ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <?= $date_start_floating->compact(); ?>
                </div>
                <div class="col">
                    <?= $date_end_floating->compact(); ?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Buscar</button>
        </form>
        <?php
    }

    private function create_select_month()
    {
        $select = new SelectComponent('invoice_month');
        $select->addOption('Seleccione un mes...', '');
        $months = [
            '01' => 'Enero',
            '02' => 'Febrero',
            '03' => 'Marzo',
            '04' => 'Abril',
            '05' => 'Mayo',
            '06' => 'Junio',
            '07' => 'Julio',
            '08' => 'Agosto',
            '09' => 'Septiembre',
            '10' => 'Octubre',
            '11' => 'Noviembre',
            '12' => 'Diciembre'
        ];
        foreach ($months as $value => $label) {
            $select->addOption($label, $value);
        }
        if (isset($_GET['invoice_month']) && $_GET['invoice_month'] !== '') {
            $select->setValue($_GET['invoice_month'] ?? '');
        } else {
            $select->setValue(date('m'));
        }
        return $select;
    }

    private function create_select_year()
    {
        $select = new SelectComponent('invoice_year');
        $select->addOption('Seleccione un año...', '');
        $current_year = date('Y');
        $years = range($current_year, $current_year - 5);
        foreach ($years as $year) {
            $select->addOption($year, $year);
        }
        if (isset($_GET['invoice_year']) && $_GET['invoice_year'] !== '') {
            $select->setValue($_GET['invoice_year'] ?? '');
        } else {
            $select->setValue(date('Y'));
        }
        return $select;
    }
}
