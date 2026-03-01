<?php
namespace CentralBooking\WooCommerce;

use CentralBooking\Data\Operator;
use WC_Coupon;

class ValidateCoupon
{
    private ?Operator $operator = null;

    public function isValid(WC_Coupon $coupon)
    {
        $operator = $this->getOperator($coupon);
        if ($operator === null) {
            return true;
        }
        $plan = $operator->getBusinessPlan();
        return $plan['counter'] < $plan['limit'];
    }

    private function getOperator(WC_Coupon $coupon)
    {
        if (!isset($this->operator)) {
            $coupon_post = get_post($coupon->get_id());
            $operator = git_operator_by_coupon($coupon_post);
            $this->operator = $operator;
        }
        return $this->operator;
    }
}