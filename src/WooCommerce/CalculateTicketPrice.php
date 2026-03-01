<?php
namespace CentralBooking\WooCommerce;

use CentralBooking\Data\Constants\PassengerConstants;
use CentralBooking\Data\Constants\PriceExtraConstants;
use CentralBooking\Data\Transport;
use WC_Product;

final class CalculateTicketPrice
{
    public function __construct()
    {
    }

    public function calculate(CartItemInterface $cartItem)
    {
        $product = $cartItem->getProduct();
        $productType = $product->get_type();
        if ($productType !== 'operator') {
            return 0;
        }
        $pax = $cartItem->getPax();
        $prices = $this->getPrices($product);
        $pricePax = $this->calculatePricePax($prices, $pax);
        $priceTransport = $this->calculatePriceTransport($cartItem->getTransport());
        $total = $pricePax + $priceTransport;
        if ($cartItem->isFlexible()) {
            $total += $prices[PriceExtraConstants::FLEXIBLE->value];
        }
        return $total;
    }

    public function getPrices(WC_Product $product)
    {
        return [
            PassengerConstants::KID->value => intval(get_post_meta($product->get_id(), 'price_kid', true)),
            PassengerConstants::RPM->value => intval(get_post_meta($product->get_id(), 'price_rpm', true)),
            PassengerConstants::STANDARD->value => intval(get_post_meta($product->get_id(), 'price_standar', true)),
            PriceExtraConstants::EXTRA->value => intval(get_post_meta($product->get_id(), 'price_extra', true)),
            PriceExtraConstants::FLEXIBLE->value => intval(get_post_meta($product->get_id(), 'price_flexible', true)),
        ];
    }

    private function calculatePriceTransport(Transport $transport)
    {
        $price = 0;
        foreach ($transport->getServices() as $service) {
            $price += $service->price / 100;
        }
        return $price;
    }

    private function calculatePricePax(array $prices, array $pax)
    {
        $kid_count = $pax[PassengerConstants::KID->value];
        $rpm_count = $pax[PassengerConstants::RPM->value];
        $extra_count = $pax[PriceExtraConstants::EXTRA->value];
        $standard_count = $pax[PassengerConstants::STANDARD->value];

        return
            $prices[PriceExtraConstants::EXTRA->value] * $extra_count +
            $prices[PassengerConstants::KID->value] * $kid_count +
            $prices[PassengerConstants::RPM->value] * $rpm_count +
            $prices[PassengerConstants::STANDARD->value] * $standard_count;
    }
}