<?php
namespace CentralBooking\Utils\ArrayParser;

use CentralBooking\Data\Route;
use CentralBooking\Data\Service;
use CentralBooking\Data\Transport;

/**
 * @extends parent<Transport>
 */
final class TransportArray implements ArrayParser
{
    /**
     * @param Transport $transport
     */
    public function get_array($transport)
    {
        return [
            'id' => $transport->id,
            'capacity' => $transport->getCapacity(),
            'crew' => $transport->getCrew() ?? [],
            'nicename' => $transport->nicename,
            'code' => $transport->code,
            'type' => [
                'slug' => $transport->type,
                'display' => $transport->type->label()
            ],
            'operator' => [
                'id' => $transport->getOperator()->getUser()->ID,
                'name' => $transport->getOperator()->getUser()->user_nicename,
            ],
            'available' => $transport->isAvailable(),
            'maintenance_dates' => $transport->getMaintenanceDates(),
            'working_days' => $transport->getWorkingDays(),
            // 'flexible' => $transport->getMeta('flexible') ?? false,
            'alias' => $transport->getAlias() ?? [],
            'photo' => $transport->getUrlPhoto() ?? 'https://imageslot.com/v1/1000x200?fg=ffffff&shadow=23272f&filetype=png',
            'custom_field' => $transport->getCustomField() ?? [
                'topic' => 'text',
                'content' => '',
            ],
            'routes' => array_map(fn(Route $route) => [
                'id' => $route->id,
                'departure_time' => $route->getDepartureTime()->format(),
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
        ];
    }
}
