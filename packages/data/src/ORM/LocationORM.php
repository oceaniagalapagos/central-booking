<?php
namespace CentralBooking\Data\ORM;

use CentralBooking\Data\Location;

/**
 * @implements ORMInterface<Location>
 */
final class LocationORM implements ORMInterface
{
    public function mapper(array $data)
    {
        $location = new Location();
        $location->id = (int) ($data['id'] ?? 0);
        $location->name = (string) ($data['name'] ?? '');
        return $location;
    }
}
