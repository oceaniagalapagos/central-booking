<?php
namespace CentralBooking\Data\Services;

use CentralBooking\Data\ORM\ORMInterface;
use CentralBooking\Data\ORM\ZoneORM;
use CentralBooking\Data\Repository\ZoneRepository;
use CentralBooking\Data\Zone;
use Exception;

class ZoneService
{
    private ZoneRepository $zoneRepository;
    private ORMInterface $orm;
    private static ?ZoneService $instance = null;

    public static function getInstance(): ZoneService
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
            $this->zoneRepository = new ZoneRepository($wpdb);
            $this->orm = new ZoneORM();
        } else {
            throw new Exception('Error en la variable global wpdb');
        }
    }

    public function save(Zone $zone)
    {
        return $this->zoneRepository->save($zone);
    }

    public function find(
        array $args = [],
        string $orderBy = 'id',
        string $order = 'ASC',
        int $limit = -1,
        int $offset = 0
    ) {
        return $this->zoneRepository->find(
            $this->orm,
            $args,
            $orderBy,
            $order,
            $limit,
            $offset
        );
    }
}
