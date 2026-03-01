<?php
namespace CentralBooking\Data\Repository;

use CentralBooking\Data\MetaManager;
use CentralBooking\Data\ORM\ORMInterface;
use CentralBooking\Data\Route;
use wpdb;

final class RouteRepository
{
    public function __construct(private readonly wpdb $wpdb)
    {
    }

    public function save(Route $entity)
    {
        $data = [
            'type' => $entity->type->slug(),
            'id_origin' => $entity->getOrigin()->id,
            'id_destiny' => $entity->getDestiny()->id,
            'arrival_time' => $entity->getArrivalTime()->format(),
            'departure_time' => $entity->getDepartureTime()->format(),
        ];
        $formats = [
            '%s',
            '%d',
            '%d',
            '%s',
            '%s'
        ];
        $tableRoutesTransports = $this->formatTable(DatabaseTable::TABLE_ROUTES_TRANSPORTS->value);
        if ($this->exists($entity->id)) {
            $this->wpdb->update(
                $this->formatTable(DatabaseTable::TABLE_ROUTES->value),
                $data,
                ['id' => $entity->id],
                $formats,
                ['%d']
            );
            $this->wpdb->delete(
                $tableRoutesTransports,
                ['id_route' => $entity->id],
                ['%d']
            );
        } else {
            $this->wpdb->insert(
                $this->formatTable(DatabaseTable::TABLE_ROUTES->value),
                $data,
                $formats
            );
            $entity->id = $this->wpdb->insert_id;
        }
        foreach ($entity->getTransports() as $transport) {
            $this->wpdb->insert(
                $tableRoutesTransports,
                [
                    'id_route' => $entity->id,
                    'id_transport' => $transport->id,
                ],
                [
                    '%d',
                    '%d',
                ]
            );
        }
        return $entity;
    }

    private function formatTable(string $table)
    {
        return $this->wpdb->prefix . $table;
    }

    private function exists(int $id): bool
    {
        $tableName = $this->formatTable(DatabaseTable::TABLE_ROUTES->value);
        $sql = "SELECT COUNT(id) from {$tableName} WHERE id = %d";
        $result = $this->wpdb->get_var(
            $this->wpdb->prepare($sql, $id)
        );
        return $result > 0;
    }

    /**
     * @param ORMInterface $orm
     * @param array $args
     * @param string $orderBy
     * @param string $order
     * @param int $limit
     * @param int $offset
     * @return ResultSetInterface<Route>
     */
    public function find(
        ORMInterface $orm,
        array $args = [],
        string $orderBy = 'id',
        string $order = 'ASC',
        int $limit = 10,
        int $offset = 0
    ): ResultSet {
        $sql = $this->getQuery(
            args: $args,
            orderBy: $orderBy,
            order: $order,
            limit: $limit,
            offset: $offset
        );
        // echo $sql;
        $results = $this->wpdb->get_results($sql, ARRAY_A);
        if ($results) {
            $items = array_map([$orm, 'mapper'], $results);
            $totalCount = $this->getTotalCount($args);
            return new ResultSet(
                $items,
                $limit,
                floor($offset / $limit) + 1,
                $totalCount,
                ceil($totalCount / $limit),
                count($results) > 0,
                true,
                true,
            );
        }
        return new ResultSet(
            [],
            0,
            0,
            0,
            0,
            false,
            false,
            false,
        );
    }

    private function getQuery(
        array $args = [],
        string $orderBy = 'id',
        string $order = 'ASC',
        int $limit = 10,
        int $offset = 0
    ) {
        $tableRoutes = $this->formatTable(DatabaseTable::TABLE_ROUTES->value);
        $tableLocations = $this->formatTable(DatabaseTable::TABLE_LOCATIONS->value);
        $tablePassengers = $this->formatTable(DatabaseTable::TABLE_PASSENGERS->value);
        $tableRoutesTransports = $this->formatTable(DatabaseTable::TABLE_ROUTES_TRANSPORTS->value);
        $tableMeta = $this->formatTable(DatabaseTable::TABLE_META->value);

        $sql = "SELECT r.*
        FROM {$tableRoutes} r
        LEFT JOIN {$tableMeta} tm ON (tm.meta_id = r.id AND tm.meta_type = %s)
        LEFT JOIN {$tableLocations} lo ON lo.id = r.id_origin
        LEFT JOIN {$tableLocations} ld ON ld.id = r.id_destiny
        -- LEFT JOIN {$tableRoutesTransports} rt ON rt.id_route = r.id
        -- LEFT JOIN {$tablePassengers} p ON p.id_route = r.id
        WHERE 1=1";
        $sql = $this->wpdb->prepare($sql, MetaManager::ROUTE);
        $filters_allowed = [
            'id' => 'r.id = %d',
            'id_passenger' => 'p.id = %d',
            'name_origin' => 'lo.name = %s',
            'name_destiny' => 'ld.name = %s',
            'id_origin' => 'r.id_origin = %d',
            'id_destiny' => 'r.id_destiny = %d',
            'id_transport' => 'rt.id_transport = %d',
            'type' => 'r.type = %s',
            'arrival_time' => "r.arrival_time = %s",
            'departure_time' => "r.departure_time = %s",
            'duration_trip' => "r.duration_trip = %s",
            'distance_km' => "r.distance_km = %.2f",
        ];

        $orders_allowed = [
            'id' => 'r.id',
            'type' => 'r.type',
            'distance' => 'r.distance',
            'name_origin' => 'lo.name',
            'name_destiny' => 'ld.name',
            'distance_km' => "r.distance_km",
            'duration_trip' => "r.duration_trip",
            'departure_time' => "r.departure_time",
        ];

        foreach ($args as $key => $value) {
            if (array_key_exists($key, $filters_allowed)) {
                $sql .= ' AND ' . $this->wpdb->prepare($filters_allowed[$key], $value);
            }
        }

        if (array_key_exists($orderBy, $orders_allowed)) {
            $sql .= " ORDER BY {$orders_allowed[$orderBy]} {$order}";
        }

        if ($limit > 0) {
            $sql .= $this->wpdb->prepare(
                " LIMIT %d",
                $limit,
            );
            if ($offset > 0) {
                $sql .= $this->wpdb->prepare(
                    " OFFSET %d",
                    $offset
                );
            }
        }

        return $sql;
    }

    protected function getTotalCount(array $args = [])
    {
        global $wpdb;
        $sql = $this->getQuery(
            args: $args,
            limit: -1
        );
        $sql = substr($sql, 10);
        $sql = "SELECT COUNT(DISTINCT r.id) {$sql}";
        // echo $sql;
        $results = $wpdb->get_var($sql);
        if ($results !== null) {
            return (int) $results;
        }
        return 0;
    }
}
