<?php
namespace CentralBooking\Data\ORM;

use CentralBooking\Data\Zone;

/**
 * @implements ORMInterface<Zone>
 */
final class ZoneORM implements ORMInterface
{
    public function mapper(array $data)
    {
        $zone = new Zone();
        $zone->id = (int) ($data['id'] ?? 0);
        $zone->name = (string) ($data['name'] ?? '');
        return $zone;
    }
}
