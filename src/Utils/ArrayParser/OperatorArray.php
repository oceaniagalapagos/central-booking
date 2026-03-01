<?php
namespace CentralBooking\Utils\ArrayParser;

use CentralBooking\Data\Route;
use CentralBooking\Data\Service;
use CentralBooking\Data\Transport;
use WP_Post;

class OperatorArray implements ArrayParser
{
    public function get_array($operator)
    {
        return [
            'id' => $operator->ID,
            'firstname' => $operator->first_name,
            'lastname' => $operator->last_name,
            'phone' => $operator->phone,
            'username' => $operator->user_login,
            'coupons' => array_map(fn(WP_Post $coupon) => [
                'id' => $coupon->ID,
                'code' => $coupon->post_title,
            ], $operator->get_coupons()),
            'business_plan' => $operator->get_business_plan(),
            'transports' => array_map(fn(Transport $transport) => [
                'id' => $transport->id,
                'code' => $transport->code,
                'capacity' => $transport->getCapacity(),
                'captain' => $transport->getCaptain(),
                'nicename' => $transport->nicename,
                'type' => $transport->type,
                'routes' => array_map(fn(Route $route) => [
                    'id' => $route->id,
                    'time' => $route->getDepartureTime()->format(),
                    'type' => $route->type,
                    'origin' => [
                        'id' => $route->getOrigin()->id,
                        'name' => $route->getOrigin()->name,
                        'zone' => [
                            'id' => $route->getOrigin()->getZone()->id,
                            'name' => $route->getOrigin()->getZone()->name,
                        ]
                    ],
                    'destiny' => [
                        'id' => $route->getDestiny()->id,
                        'name' => $route->getDestiny()->name,
                        'zone' => [
                            'id' => $route->getDestiny()->getZone()->id,
                            'name' => $route->getDestiny()->getZone()->name,
                        ]
                    ],
                ], $transport->getRoutes()),
                'services' => array_map(fn(Service $service) => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'price' => $service->price,
                    'icon' => $service->icon,
                ], $transport->getServices()),
            ], $operator->get_transports()),
        ];
    }
}
