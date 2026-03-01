<?php
namespace CentralBooking\Data\Services;

use CentralBooking\Data\ORM\ORMInterface;
use CentralBooking\Data\ORM\ServiceORM;
use CentralBooking\Data\Repository\ServiceRepository;
use CentralBooking\Data\Service;
use Exception;

final class ServiceService
{
    private ServiceRepository $serviceRepository;
    private ORMInterface $serviceORM;
    private static ?ServiceService $instance = null;
    public static function getInstance(): ServiceService
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
            $this->serviceRepository = new ServiceRepository($wpdb);
            $this->serviceORM = new ServiceORM();
        } else {
            throw new Exception('Error en la variable global wpdb');
        }
    }

    public function save(Service $service)
    {
        if (empty($service->name)) {
            throw new Exception('El nombre del servicio no puede estar vacío');
        }
        if ($service->price < 0) {
            throw new Exception('El precio del servicio debe ser mayor o igual que cero');
        }
        return $this->serviceRepository->save($service);
    }

    public function findAll(): array
    {
        $result = $this->serviceRepository->find(
            orm: $this->serviceORM,
            orderBy: 'id',
            order: 'ASC'
        );
        return $result->getItems();
    }

    public function find(
        array $args = [],
        string $orderBy = 'id',
        string $order = 'ASC',
        int $limit = -1,
        int $offset = 0
    ) {
        return $this->serviceRepository->find(
            orm: $this->serviceORM,
            args: $args,
            orderBy: $orderBy,
            order: $order,
            limit: $limit,
            offset: $offset
        );
    }
}
