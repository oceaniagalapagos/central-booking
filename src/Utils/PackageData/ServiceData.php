<?php
namespace CentralBooking\Utils\PackageData;

use CentralBooking\Data\Service;
use CentralBooking\Data\Transport;

/**
 * @implements parent<Service>
 */
class ServiceData implements PackageData
{
    /**
     * @param array<int> $transports
     */
    public function __construct(
        public readonly int $price = 0,
        public readonly string $name = '',
        public readonly string $icon = '',
        public readonly array $transports = [],
    ) {
    }

    public function get_data()
    {
        $entity = new Service;

        $entity->name = $this->name;
        $entity->price = $this->price;
        $entity->icon = $this->icon;


        foreach ($this->transports as $id_transport) {
            $transport = new Transport();
            $transport->id = $id_transport;
            $entity->setTransports([...$entity->getTransports(), $transport]);
        }

        return $entity;
    }
}
