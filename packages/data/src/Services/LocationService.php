<?php
namespace CentralBooking\Data\Services;

use CentralBooking\Data\Location;
use CentralBooking\Data\ORM\LocationORM;
use CentralBooking\Data\ORM\ORMInterface;
use CentralBooking\Data\Repository\LocationRepository;
use Exception;

class LocationService
{
    private LocationRepository $locationRepository;
    private ORMInterface $orm;
    private static ?LocationService $instance = null;

    public static function getInstance(): LocationService
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $wpdb = $GLOBALS['wpdb'];
        if ($wpdb) {
            $this->locationRepository = new LocationRepository($wpdb);
            $this->orm = new LocationORM();
        } else {
            throw new Exception('Error en la variable gloabl wpdb');
        }
    }

    public function save(Location $location)
    {
        return $this->locationRepository->save($location);
    }

    public function find(array $args = [], string $orderBy = 'id', string $order = 'ASC', int $limit = -1, int $offset = 0)
    {
        return $this->locationRepository->find(
            $this->orm,
            $args,
            $orderBy,
            $order,
            $limit,
            $offset
        );
    }
}
