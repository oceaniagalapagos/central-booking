<?php
namespace CentralBooking\Data\Repository;

use CentralBooking\Data\MetaManager;
use CentralBooking\Data\Operator;
use WP_Post;

class CouponRepository
{
    public function assignCouponToOperator(WP_Post $coupon, Operator $operator)
    {
        MetaManager::setMeta(
            MetaManager::COUPON,
            $coupon->ID,
            'coupon_assigned_operator',
            $operator->getUser()->ID
        );
        return $coupon;
    }

    public function unassignCouponToOperator(WP_Post $coupon, Operator $operator)
    {
        MetaManager::removeMeta(
            MetaManager::COUPON,
            $coupon->ID,
            'coupon_assigned_operator',
        );
    }

    /**
     * @param Operator $operator
     * @return array<WP_Post>
     */
    public function findCouponsByOperator(Operator $operator)
    {
        global $wpdb;
        $table = DatabaseTable::TABLE_META->value;
        $coupon_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT meta_id FROM {$wpdb->prefix}{$table} WHERE meta_type = %s AND meta_key = 'coupon_assigned_operator' AND meta_value = %d",
                MetaManager::COUPON,
                $operator->getUser()->ID
            )
        );
        $coupons = [];
        foreach ($coupon_ids as $coupon_id) {
            $coupon = get_post($coupon_id);
            if ($coupon !== null && $coupon->post_type === 'shop_coupon') {
                $coupons[] = $coupon;
            }
        }
        return $coupons;
    }

    /**
     * 
     * @return array<WP_Post>
     */
    public function findAll()
    {
        $args = [
            'post_type' => 'shop_coupon',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ];
        return get_posts($args);
    }

    public function find(int $id): WP_Post|null
    {
        $coupon = get_post($id);
        if ($coupon !== null && $coupon->post_type === 'shop_coupon') {
            return is_object($coupon) ? $coupon : null;
        }
        return null;
    }
}
