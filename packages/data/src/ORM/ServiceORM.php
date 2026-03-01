<?php
namespace CentralBooking\Data\ORM;

use CentralBooking\Data\Service;

/**
 * @implements ORMInterface<Service>
 */
final class ServiceORM implements ORMInterface
{
    public function mapper(array $data)
    {
        $route = new Service();
        $route->id = (int) ($data['id'] ?? 0);
        $route->name = (string) ($data['name'] ?? '');
        $route->price = (int) ($data['price'] ?? 0);
        $route->icon = (string) ($data['icon'] ?? '');
        return $route;
    }
}
