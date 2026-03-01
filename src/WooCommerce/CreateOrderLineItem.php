<?php
namespace CentralBooking\WooCommerce;

use CentralBooking\Data\Constants\PriceExtraConstants;
use CentralBooking\Data\Constants\PassengerConstants;
use WC_Order_Item;

class CreateOrderLineItem
{
    public function add_line_item(WC_Order_Item $item, array $values)
    {
        if ($values['data']->product_type !== 'operator') {
            return;
        }
        $cart_ticket = $values['cart_ticket'] ?? null;
        if ($cart_ticket !== null && ($cart_ticket instanceof CartItemInterface)) {
            $this->addMetaData($item, $cart_ticket);
        }
    }
    
    private function addMetaData(WC_Order_Item $item, CartItemInterface $cartItem)
    {
        $item->add_meta_data('Trayecto', $cartItem->getRoute()->getOrigin()->name . ' » ' . $cartItem->getRoute()->getDestiny()->name, true);
        $item->add_meta_data('Viaje', $cartItem->getDateTrip()->pretty() . ' - ' . $cartItem->getRoute()->getDepartureTime()->pretty(), true);
        $item->add_meta_data('Flexible', $cartItem->isFlexible() ? 'Sí' : 'No', true);
        $item->add_meta_data('Transporte', $cartItem->getTransport()->nicename, true);
        if ($cartItem->getPax()[PassengerConstants::STANDARD->value] > 0) {
            $item->add_meta_data('Pax Estandar', $cartItem->getPax()[PassengerConstants::STANDARD->value], true);
        }
        if ($cartItem->getPax()[PassengerConstants::KID->value] > 0) {
            $item->add_meta_data('Pax Menor de Edad', $cartItem->getPax()[PassengerConstants::KID->value], true);
        }
        if ($cartItem->getPax()[PassengerConstants::RPM->value] > 0) {
            $item->add_meta_data('Pax Movolidad Reducida', $cartItem->getPax()[PassengerConstants::RPM->value], true);
        }
        if ($cartItem->getPax()[PriceExtraConstants::EXTRA->value] > 0) {
            $item->add_meta_data('Equipaje Extra', $cartItem->getPax()[PriceExtraConstants::EXTRA->value], true);
        }
        $item->add_meta_data('_original_data', serialize($cartItem));
    }
}