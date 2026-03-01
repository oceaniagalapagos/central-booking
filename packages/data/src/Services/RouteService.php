<?php
namespace CentralBooking\Data\Services;

use CentralBooking\Data\ORM\ORMInterface;
use CentralBooking\Data\ORM\RouteORM;
use CentralBooking\Data\Repository\RouteRepository;
use CentralBooking\Data\Route;
use Exception;

class RouteService
{
    private RouteRepository $routeRepository;
    private ORMInterface $routeORM;
    private static ?RouteService $instance = null;

    /**
     * @return RouteService
     */
    public static function getInstance(): RouteService
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
            $this->routeRepository = new RouteRepository($wpdb);
            $this->routeORM = new RouteORM();
        } else {
            throw new Exception('Error en la variable global wpdb');
        }
    }

    public function save(Route $route)
    {
        if (!$this->validateTransports($route)) {
            throw new Exception('Los transportes asociados no coinciden con el tipo de ruta');
        }
        if ($route->getOrigin()->id === $route->getDestiny()->id) {
            throw new Exception('El origen y destino no pueden ser iguales');
        }
        return $this->routeRepository->save($route);
    }

    private function validateTransports(Route $route)
    {
        foreach ($route->getTransports() as $transport) {
            if ($transport->type !== $route->type) {
                return false;
            }
        }
        return true;
    }

    public function find(array $args = [], int $limit = -1, int $offset = 0, string $orderBy = 'id', string $order = 'ASC')
    {
        return $this->routeRepository->find(
            $this->routeORM,
            $args,
            $orderBy,
            $order,
            $limit,
            $offset
        );
    }
}
