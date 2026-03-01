<?php
namespace CentralBooking\Data\Repository;

use CentralBooking\Data\MetaManager;
use CentralBooking\Data\ORM\ORMInterface;
use CentralBooking\Data\Transport;
use CentralBooking\Data\Repository\DatabaseTable;
use wpdb;

final class TransportRepository
{
    public function __construct(private readonly wpdb $wpdb)
    {
    }

    public function save(Transport $transport)
    {
        $data = [
            'code' => $transport->code,
            'type' => $transport->type->value,
            'nicename' => $transport->nicename,
            'id_operator' => $transport->getOperator()->getUser()->ID,
        ];

        $formats = [
            '%s',
            '%s',
            '%s',
            '%d',
        ];

        if ($this->exists($transport->id)) {
            $this->wpdb->update(
                $this->formatTable(DatabaseTable::TABLE_TRANSPORTS->value),
                $data,
                ['id' => $transport->id],
                $formats,
                ['%d']
            );

            $this->wpdb->delete(
                $this->formatTable(DatabaseTable::TABLE_TRANSPORTS_SERVICES->value),
                ['id_transport' => $transport->id],
                ['%d']
            );

            $this->wpdb->delete(
                $this->formatTable(DatabaseTable::TABLE_ROUTES_TRANSPORTS->value),
                ['id_transport' => $transport->id],
                ['%d']
            );

        } else {
            $this->wpdb->insert(
                $this->formatTable(DatabaseTable::TABLE_TRANSPORTS->value),
                $data,
                $formats
            );
            $transport->id = $this->wpdb->insert_id;
        }

        foreach ($transport->getServices() as $service) {
            $this->wpdb->insert(
                $this->formatTable(DatabaseTable::TABLE_TRANSPORTS_SERVICES->value),
                [
                    'id_transport' => $transport->id,
                    'id_service' => $service->id,
                ],
                ['%d', '%d']
            );
        }

        foreach ($transport->getRoutes() as $route) {
            $this->wpdb->insert(
                $this->formatTable(DatabaseTable::TABLE_ROUTES_TRANSPORTS->value),
                [
                    'id_transport' => $transport->id,
                    'id_route' => $route->id,
                ],
                ['%d', '%d']
            );
        }

        $transport->saveMeta();

        return $transport;
    }

    private function formatTable(string $table)
    {
        return $this->wpdb->prefix . $table;
    }

    private function exists(int $id): bool
    {
        $tableName = $this->formatTable(DatabaseTable::TABLE_TRANSPORTS->value);
        $sql = $this
            ->wpdb
            ->prepare(
                "SELECT COUNT(id) from {$tableName} WHERE id = %d",
                $id
            );
        $result = $this->wpdb->get_var($sql);
        return ((int) $result) > 0;
    }

    /**
     * @param ORMInterface<Transport> $orm
     * @param array $args
     * @param string $orderBy
     * @param string $order
     * @param int $limit
     * @param int $offset
     * @return ResultSetInterface<Transport>
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
        $usersTable = $this->formatTable('users');
        $metaTable = $this->formatTable(DatabaseTable::TABLE_META->value);
        $routesTable = $this->formatTable(DatabaseTable::TABLE_ROUTES->value);
        $locationsTable = $this->formatTable(DatabaseTable::TABLE_LOCATIONS->value);
        $transportsTable = $this->formatTable(DatabaseTable::TABLE_TRANSPORTS->value);
        $passengersTable = $this->formatTable(DatabaseTable::TABLE_PASSENGERS->value);
        $routesTransportsTable = $this->formatTable(DatabaseTable::TABLE_ROUTES_TRANSPORTS->value);
        $transportsServicesTable = $this->formatTable(DatabaseTable::TABLE_TRANSPORTS_SERVICES->value);

        $sql = "SELECT t.*
            FROM {$transportsTable} t
            LEFT JOIN {$usersTable} u ON u.id = t.id_operator
            LEFT JOIN {$passengersTable} p ON p.id_transport = t.id
            LEFT JOIN {$metaTable} tm ON (tm.meta_id = t.id AND tm.meta_type = %s)
            LEFT JOIN {$routesTransportsTable} rt ON rt.id_transport = t.id
            LEFT JOIN {$routesTable} r ON r.id = rt.id_route
            LEFT JOIN {$locationsTable} lo ON lo.id = r.id_origin
            LEFT JOIN {$locationsTable} ld ON ld.id = r.id_destiny
            LEFT JOIN {$transportsServicesTable} ts ON ts.id_transport = t.id
            WHERE 1 = 1";
        $sql = $this->wpdb->prepare($sql, MetaManager::TRANSPORT);
        $filters = [
            'id' => 't.id = %d',
            'type' => 't.type = %s',
            'code' => 't.code = %s',
            'flexible' => "tm.meta_key = 'flexible' AND tm.meta_value = %s",
            'nicename' => 't.nicename = %s',
            'id_zone_origin' => 'zo.id = %d',
            'id_zone_destiny' => 'zd.id = %d',
            'name_zone_origin' => 'zo.name = %s',
            'name_zone_destiny' => 'zd.name = %s',
            'id_origin' => 'lo.id = %d',
            'id_destiny' => 'ld.id = %d',
            'name_origin' => 'lo.name = %s',
            'working_day' => "tm.meta_key = 'working_days' AND tm.meta_value LIKE %s",
            'name_destiny' => 'ld.name = %s',
            'id_service' => 'ts.id_service = %d',
            'id_route' => 'rt.id_route = %d',
            'id_operator' => 't.id_operator = %d',
            'id_passenger' => 'p.id = %d',
            'username_operator' => 'u.user_login = %s',
        ];

        $orders = [
            'id' => 't.id',
            'code' => 't.code',
            'nicename' => 't.nicename',
            'id_operator' => 't.id_operator',
            'type' => 't.type',
        ];

        foreach ($args as $key => $value) {
            if (isset($filters[$key])) {
                $sql .= $this->wpdb->prepare(
                    " AND {$filters[$key]}",
                    $value
                );
            }
        }

        $sql .= " GROUP BY t.id";

        if (isset($orders[$orderBy])) {
            $sql .= " ORDER BY {$orders[$orderBy]} {$order}";
        }

        if ($limit > 0) {
            $sql .= $this->wpdb->prepare(
                " LIMIT %d",
                $limit,
                $offset
            );
            if ($offset > 0) {
                $sql .= $this->wpdb->prepare(
                    " OFFSET %d",
                    $limit,
                    $offset
                );
            }
        }

        return $sql;
    }

    public function getTotalCount(array $args = []): int
    {
        global $wpdb;
        $sql = $this->getQuery(
            args: $args,
            limit: -1
        );
        $sql = substr($sql, 19);
        $sql = "SELECT COUNT(DISTINCT t.id) {$sql}";
        $results = $wpdb->get_var($sql);
        if ($results !== null) {
            return (int) $results;
        }
        return 0;
    }
}