<?php
namespace CentralBooking\Data\ORM;

use CentralBooking\Data\Constants\TypeOperation;
use CentralBooking\Data\Route;
use CentralBooking\Data\Time;

/**
 * @implements ORMInterface<Route>
 */
final class RouteORM implements ORMInterface
{
    public function mapper(array $data)
    {
        $route = new Route();
        $route->id = (int) ($data['id'] ?? 0);
        $route->type = TypeOperation::fromSlug($data['type'] ?? '') ?? TypeOperation::MARINE;
        $route->setArrivalTime(new Time((string) ($data['arrival_time'] ?? '00:00:00')));
        $route->setDepartureTime(new Time((string) ($data['departure_time'] ?? '00:00:00')));
        return $route;
    }
}
