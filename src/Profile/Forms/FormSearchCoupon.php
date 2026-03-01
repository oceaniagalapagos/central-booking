<?php
namespace CentralBooking\Profile\Forms;

use CentralBooking\Data\Constants\UserRole;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputComponent;
use CentralBooking\GUI\SelectComponent;
use CentralBooking\Implementation\GUI\CouponSelect;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Implementation\Temp\MessageTemporal;

class FormSearchCoupon implements DisplayerInterface
{
    private SelectComponent $coupon_select;
    private InputComponent $date_start_input;
    private InputComponent $date_end_input;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $is_operator = git_current_user_has_role(UserRole::OPERATOR);

        $this->date_end_input = git_input_field([
            'name' => 'date_end',
            'type' => 'date',
            'required' => true,
            'value' => $_GET['date_end'] ?? null,
        ]);
        $this->date_start_input = git_input_field([
            'name' => 'date_start',
            'type' => 'date',
            'required' => true,
            'value' => $_GET['date_start'] ?? null,
        ]);

        $this->coupon_select = (new CouponSelect('coupon', $is_operator ? get_current_user_id() : null))->create();
        $this->coupon_select->setRequired(true);
        $this->coupon_select->setValue($_GET['coupon'] ?? null);
    }

    public function render()
    {
        $this->showMessage();
        ?>
        <div class="git-profile-section">
            <div class="git-profile-card">
                <div class="git-profile-card-body">
                    <form method="get" class="git-search-form">
                        <input type="hidden" name="tab" value="<?= $_GET['tab'] ?? '' ?>">
                        <div class="git-form-group">
                            <?php echo $this->coupon_select->getLabel('Cupón')->compact(); ?>
                            <?php echo $this->coupon_select->compact(); ?>
                        </div>

                        <div class="git-form-row">
                            <div class="git-form-group git-form-group-half">
                                <?php echo $this->date_start_input->getLabel('Fecha de inicio')->compact(); ?>
                                <?php echo $this->date_start_input->compact(); ?>
                            </div>
                            <div class="git-form-group git-form-group-half">
                                <?php echo $this->date_end_input->getLabel('Fecha de fin')->compact(); ?>
                                <?php echo $this->date_end_input->compact(); ?>
                            </div>
                        </div>

                        <div class="git-form-actions">
                            <button type="submit" class="git-btn git-btn-primary">
                                Buscar
                            </button>
                            <button type="reset" class="git-btn git-btn-secondary">
                                Limpiar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
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
