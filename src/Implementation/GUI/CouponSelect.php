<?php
namespace CentralBooking\Implementation\GUI;

use WP_Post;

final class CouponSelect
{
    /**
     * @var array<WP_Post>
     */
    private array $coupons;

    public function __construct(private string $name = 'coupon', ?int $operator = null)
    {
        if ($operator === null) {
            $this->coupons = git_coupons();
        } else {
            $operator = git_operator_by_id($operator);
            if ($operator === null) {
                $this->coupons = [];
            } else {
                $this->coupons = $operator->getCoupons();
            }
        }
    }

    public function create(bool $multiple = false)
    {

        $selectComponent = $multiple ? git_multiselect_field(['name' => $this->name]) : git_select_field(['name' => $this->name]);

        $selectComponent->addOption('Seleccione...', '');

        foreach ($this->coupons as $coupon) {
            $selectComponent->addOption(
                $coupon->post_title,
                $coupon->ID
            );
        }

        return $selectComponent;
    }
}