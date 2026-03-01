<?php
namespace CentralBooking\WooCommerce;

use CentralBooking\Data\Constants\PassengerConstants;
use CentralBooking\Data\Constants\PriceExtraConstants;

class ProductItemCart
{
    public function itemCart(array $cartItem)
    {
        if ($cartItem['data']->get_type() !== 'operator') {
            return [];
        }
        $cartTicket = $cartItem['cart_ticket'] ?? null;
        if ($cartTicket !== null && ($cartTicket instanceof CartItemInterface)) {
            return $this->getItemData($cartTicket);
        }
        return [];
    }

    private function getItemData(CartItemInterface $item)
    {
        $itemData = [];
        $route = $item->getRoute();
        $transport = $item->getTransport();
        $itemData[] = [
            'name' => 'Ruta',
            'value' => "{$route->getOrigin()->name} » {$route->getDestiny()->name}",
        ];
        $itemData[] = [
            'name' => 'Viaje',
            'value' => $item->getDateTrip()->pretty() . ' - ' . $route->getDepartureTime()->pretty()
        ];
        $itemData[] = [
            'name' => 'Transporte',
            'value' => $transport->nicename
        ];
        if ($item->getPax()[PassengerConstants::STANDARD->value] > 0) {
            $itemData[] = [
                'name' => 'Pax Estandar',
                'value' => $item->getPax()[PassengerConstants::STANDARD->value]
            ];
        }
        if ($item->getPax()[PassengerConstants::KID->value] > 0) {
            $itemData[] = [
                'name' => 'Pax Menores de Edad',
                'value' => $item->getPax()[PassengerConstants::KID->value]
            ];
        }
        if ($item->getPax()[PassengerConstants::RPM->value] > 0) {
            $itemData[] = [
                'name' => 'Pax Movilidad Reducida',
                'value' => $item->getPax()[PassengerConstants::RPM->value]
            ];
        }
        if ($item->getPax()[PriceExtraConstants::EXTRA->value] > 0) {
            $itemData[] = [
                'name' => 'Equipaje extra',
                'value' => $item->getPax()[PriceExtraConstants::EXTRA->value]
            ];
        }
        $itemData[] = [
            'name' => 'Ticket flexible',
            'value' => $item->isFlexible() ? 'Sí' : 'No'
        ];
        return $itemData;
    }
}