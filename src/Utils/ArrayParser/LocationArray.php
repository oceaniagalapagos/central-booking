<?php
namespace CentralBooking\Utils\ArrayParser;

use CentralBooking\Data\Location;

/**
 * @extends parent<Location>
 */
class LocationArray implements ArrayParser
{
    public function get_array($location)
    {
        return [
            'id' => $location->id,
            'name' => $location->name,
            'zone' => [
                'id' => $location->get_zone()->id,
                'name' => $location->get_zone()->name,
            ]
        ];
    }
}
