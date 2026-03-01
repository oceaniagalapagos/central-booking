<?php
namespace CentralBooking\Utils\ArrayParser;

use CentralBooking\Data\Route;
use CentralBooking\Data\Transport;

/**
 * @extends parent<Route>
 */
class RouteArray implements ArrayParser
{
    /**
     * @param Route $route
     * @return array{departure_time: mixed, destiny: array, distance_km: mixed, duration_trip: mixed, id: mixed, origin: array, transports: array, type: mixed}
     */
    public function get_array($route)
    {
        return [
            'id' => $route->id,
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
            // 'duration_trip' => $route->duration_trip,
            'departure_time' => $route->getDepartureTime()->format(),
            // 'distance_km' => $route->get_distance_km(),
            'transports' => array_map(fn(Transport $transport) => [
                'id' => $transport->id,
                'nicename' => $transport->nicename,
                'code' => $transport->code,
                'type' => $transport->type
            ], $route->getTransports()),
        ];
    }
}
