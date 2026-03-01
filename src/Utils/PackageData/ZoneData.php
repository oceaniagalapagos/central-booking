<?php
namespace CentralTickets\Services\PackageData;

use CentralTickets\Location;
use CentralTickets\Zone;

class ZoneData implements PackageData
{
    public function __construct(
        public readonly string $name = '',
        public readonly array $locations = [],
    ) {
    }

    public function get_data()
    {
        $zone = new Zone;
        $zone->name = $this->name;

        $zone->set_locations(array_map(
            function (int $id_location) {
                $location = new Location;
                $location->id = $id_location;
                return $location;
            },
            $this->locations
        ));

        return $zone;
    }
}
