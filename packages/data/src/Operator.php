<?php
namespace CentralBooking\Data;

use CentralBooking\Data\Repository\LazyLoader;
use InvalidArgumentException;
use WP_User;
use WP_Post;

class Operator
{
    /**
     * @var array<WP_Post>
     */
    private array $coupons;
    /**
     * @var array<Transport>
     */
    private array $transports;
    private WP_User $user;
    private array $metadata = [];
    private bool $couponsLoaded = false;

    public function getMeta(string $key)
    {
        if (isset($this->metadata[$key]) === false) {
            $value = MetaManager::getMeta(
                MetaManager::OPERATOR,
                $this->getUser()->ID,
                $key
            );
            $this->metadata[$key] = $value;
        }
        return $this->metadata[$key];
    }

    public function setMeta(string $key, mixed $value)
    {
        $this->metadata[$key] = $value;
    }

    public function saveMeta()
    {
        MetaManager::setMetadata(
            MetaManager::OPERATOR,
            $this->getUser()->ID,
            $this->metadata
        );
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(WP_User $user)
    {
        $this->user = $user;
    }

    /**
     * @return array{counter: int, limit: int}
     */
    public function getBusinessPlan()
    {
        $businessPlan = $this->getMeta('business_plan');
        if (is_array($businessPlan) === false) {
            return ['limit' => 0, 'counter' => 0];
        }
        return [
            'limit' => $businessPlan['limit'] ?? 0,
            'counter' => $businessPlan['counter'] ?? 0,
        ];
    }

    public function setBusinessPlan(int $limit, int $counter)
    {
        if ($limit < $counter) {
            throw new InvalidArgumentException('Business plan limit cannot be less than counter.');
        }
        $this->setMeta('business_plan', [
            'limit' => $limit,
            'counter' => $counter,
        ]);
    }

    public function getCoupons()
    {
        if ($this->couponsLoaded === false) {
            $this->coupons = LazyLoader::loadCouponsByOperator($this);
            $this->couponsLoaded = true;
        }

        return $this->coupons;
    }

    /**
     * @param array<WP_Post> $coupons
     * @return void
     */
    public function setCoupons(array $coupons)
    {
        $this->coupons = $coupons;
        $this->couponsLoaded = true;
    }

    public function getTransports()
    {
        if (!isset($this->transports)) {
            $this->transports = LazyLoader::loadTransportsByOperator($this);
        }
        return $this->transports;
    }

    public function setTransports(array $transports)
    {
        $this->transports = $transports;
    }

    public function setPhone(string $phone)
    {
        $this->setMeta('phone', $phone);
    }

    public function getPhone()
    {
        return (string) ($this->getMeta('phone') ?? '');
    }

    public function getBrandMedia()
    {
        return (string) ($this->getMeta('brand_media') ?? '');
    }

    public function setBrandMedia(string $brand_media)
    {
        $this->setMeta('brand_media', $brand_media);
    }

    public function save()
    {
        git_operator_save($this);
    }
}
