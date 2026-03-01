<?php
namespace CentralBooking\Utils\ArrayParser;

use CentralBooking\Data\Location;
use CentralBooking\Data\Zone;

/**
 * @extends parent<Zone>
 */
class ZoneArray implements ArrayParser
{
    public function get_array($zone)
    {
        return [
            'id' => $zone->id,
            'name' => $zone->name,
            'locations' => array_map(fn(Location $location) => [
                'id' => $location->id,
                'name' => $location->name,
            ], $zone->getLocations())
        ];
    }
}
