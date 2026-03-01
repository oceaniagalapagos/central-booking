<?php
namespace CentralBooking\Utils\ArrayParser;

use CentralBooking\Data\Passenger;
use CentralBooking\Data\Service;

/**
 * @implements parent<Passenger>
 */
class PassengerArray implements ArrayParser
{
    public function get_array($passenger)
    {
        return [
            'id' => $passenger->id,
            'name' => $passenger->name,
            'birthday' => $passenger->birthday,
            'date_trip' => $passenger->date_trip,
            'type_document' => $passenger->type_document,
            'data_document' => $passenger->data_document,
            'nationality' => $passenger->nationality,
            'served' => $passenger->served,
            'type' => $passenger->type,
            'approved' => $passenger->approved,
            'ticket' => [
                'id' => $passenger->get_ticket()->id,
                'phone' => $passenger->get_ticket()->get_order()->get_billing_phone(),
                'buyer' => $passenger->get_ticket()->get_order()->get_billing_first_name(),
                'date_creation' => $passenger->get_ticket()->get_order()->get_date_created()->format('Y-m-d H:i:s'),
                'order' => $passenger->get_ticket()->get_order()->get_id(),
                'coupon' => $passenger->get_ticket()->get_coupon() ? [
                    'id' => $passenger->get_ticket()->get_coupon()->ID,
                    'code' => $passenger->get_ticket()->get_coupon()->post_title,
                ] : null,
                'flexible' => $passenger->get_ticket()->flexible,
                'status' => $passenger->get_ticket()->status,
            ],
            'route' => [
                'id' => $passenger->get_route()->id,
                'departure_time' => $passenger->get_route()->departure_time,
                'duration' => $passenger->get_route()->duration_trip,
                'origin' => [
                    'id' => $passenger->get_route()->get_origin()->id,
                    'name' => $passenger->get_route()->get_origin()->name,
                    'zone' => [
                        'id' => $passenger->get_route()->get_origin()->get_zone()->id,
                        'name' => $passenger->get_route()->get_origin()->get_zone()->name
                    ]
                ],
                'destiny' => [
                    'id' => $passenger->get_route()->get_destiny()->id,
                    'name' => $passenger->get_route()->get_destiny()->name,
                    'zone' => [
                        'id' => $passenger->get_route()->get_destiny()->get_zone()->id,
                        'name' => $passenger->get_route()->get_destiny()->get_zone()->name
                    ]
                ],
                'type' => $passenger->get_route()->type
            ],
            'transport' => [
                'id' => $passenger->get_transport()->id,
                'nicename' => $passenger->get_transport()->nicename,
                'code' => $passenger->get_transport()->code,
                'crew' => $passenger->get_transport()->get_meta('crew'),
                'capacity' => $passenger->get_transport()->get_meta('capacity'),
                'type' => $passenger->get_transport()->type,
                'services' => array_map(fn(Service $service) => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'price' => $service->price,
                    'icon' => $service->icon,
                ], $passenger->get_transport()->get_services()),
            ],
        ];
    }
}
