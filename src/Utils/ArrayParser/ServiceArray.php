<?php
namespace CentralBooking\Utils\ArrayParser;

use CentralBooking\Data\Service;
use CentralBooking\Data\Transport;

/**
 * @extends parent<Service>
 */
class ServiceArray implements ArrayParser
{
    public function get_array($service)
    {
        return [
            'id' => $service->id,
            'name' => $service->name,
            'price' => $service->price,
            'icon' => $service->icon,
            'transports' => array_map(fn(Transport $transport) => [
                'id' => $transport->id,
                'nicename' => $transport->nicename,
                'capacity' => $transport->getCapacity(),
                'crew' => $transport->getCrew(),
                'code' => $transport->code,
                'operator' => [
                    'id' => $transport->getOperator()->getUser()->ID,
                    'name' => $transport->getOperator()->getUser()->user_nicename,
                ],
                'type' => $transport->type,
            ], $service->getTransports()),
        ];
    }
}
