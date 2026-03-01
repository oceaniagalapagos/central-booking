<?php
namespace CentralBooking\Data\Services;

use CentralBooking\Data\MetaManager;
use CentralBooking\Data\Operator;
use CentralBooking\Data\Repository\CouponRepository;
use CentralBooking\Data\Repository\LazyLoader;
use CentralBooking\Data\Repository\OperatorRepository;
use WP_Post;

class OperatorService
{
    private CouponRepository $coupon_repository;
    private OperatorRepository $operator_repository;

    private static ?OperatorService $instance = null;

    public static function getInstance(): OperatorService
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->operator_repository = new OperatorRepository();
        $this->coupon_repository = new CouponRepository();
    }

    public function save(Operator $operator)
    {
        wp_update_user($operator->getUser());
        $operator->saveMeta();
        $couponRepository = new CouponRepository();
        $coupons = LazyLoader::loadCouponsByOperator($operator);
        foreach ($coupons as $coupon) {
            $couponRepository->unassignCouponToOperator($coupon, $operator);
        }
        foreach ($operator->getCoupons() as $coupon) {
            $couponRepository->assignCouponToOperator($coupon, $operator);
        }
        return $operator;
    }

    public function findByCoupon(WP_Post $coupon)
    {
        $operatorId = MetaManager::getMeta(
            MetaManager::COUPON,
            $coupon->ID,
            'coupon_assigned_operator',
        );
        if ($operatorId === null) {
            return null;
        }
        return $this->operator_repository->findById((int) $operatorId);
    }

    function findById(int $id)
    {
        return $this->operator_repository->findById($id);
    }

    public function findAll()
    {
        return $this->operator_repository->findAll();
    }
}
