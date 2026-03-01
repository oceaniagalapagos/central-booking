<?php
namespace CentralBooking\Data\Repository;

use CentralBooking\Data\ORM\ORMInterface;
use CentralBooking\Data\Service;
use wpdb;

class ServiceRepository
{
    /**
     * @param wpdb $wpdb
     */
    public function __construct(private readonly wpdb $wpdb)
    {
    }

    private function formatTable(string $table)
    {
        return $this->wpdb->prefix . $table;
    }

    public function save(Service $service)
    {
        $data = [
            'price' => $service->price,
            'name' => $service->name,
            'icon' => $service->icon
        ];
        $format = ['%d', '%s', '%s'];

        if ($this->exists($service->id)) {
            $this->wpdb->update(
                $this->formatTable(DatabaseTable::TABLE_SERVICES->value),
                $data,
                ['id' => $service->id],
                $format,
                ['%d']
            );
            $this->wpdb->delete(
                $this->formatTable(DatabaseTable::TABLE_TRANSPORTS_SERVICES->value),
                ['id_service' => $service->id],
                ['%d']
            );
        } else {
            $this->wpdb->insert(
                $this->formatTable(DatabaseTable::TABLE_SERVICES->value),
                $data,
                $format
            );
            $service->id = $this->wpdb->insert_id;
        }

        foreach ($service->getTransports() as $transport) {
            $this->wpdb->insert(
                $this->formatTable(DatabaseTable::TABLE_TRANSPORTS_SERVICES->value),
                [
                    'id_service' => $service->id,
                    'id_transport' => $transport->id
                ],
                ['%d', '%d']
            );
        }

        return $service;
    }

    private function exists(int $id): bool
    {
        $tableName = $this->formatTable(DatabaseTable::TABLE_SERVICES->value);
        $sql = "SELECT COUNT(id) from {$tableName} WHERE id = %d";
        $result = $this->wpdb->get_var(
            $this->wpdb->prepare($sql, $id)
        );
        return $result > 0;
    }

    /**
     * @param ORMInterface<Service> $orm
     * @param array $args
     * @param string $orderBy
     * @param string $order
     * @param int $limit
     * @param int $offset
     * @return ResultSetInterface<Service>
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
            return new ResultSet(
                $items,
                $limit,
                $limit,
                $this->getTotalCount(),
                $this->getTotalCount(),
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

    /**
     * @param ORMInterface<Service> $orm
     * @param int $id
     * @return Service|null
     */
    public function findById(ORMInterface $orm, int $id)
    {
        return $this->findFirst($orm, ['id' => $id]);
    }

    /**
     * @param ORMInterface<Service> $orm
     * @param array $args
     * @return Service|null
     */
    public function findFirst(ORMInterface $orm, array $args = [])
    {
        $result = $this->find($orm, $args, limit: 1, offset: 0);
        if ($result->hasItems()) {
            return $result->getItems()[0];
        }
        return null;
    }

    private function getQuery(
        array $args = [],
        string $orderBy = 'id',
        string $order = 'ASC',
        int $limit = 10,
        int $offset = 0
    ) {
        $serviceTable = $this->formatTable(DatabaseTable::TABLE_SERVICES->value);
        $transportServiceTable = $this->formatTable(DatabaseTable::TABLE_TRANSPORTS_SERVICES->value);

        $sql = "SELECT s.* FROM {$serviceTable} s
            LEFT JOIN {$transportServiceTable} ts ON s.id = ts.id_service
            WHERE 1 = 1";

        $filters = [
            'id' => 's.id = %d',
            'name' => 's.name = %s',
            'id_transport' => 'ts.id_transport = %d',
            'price' => 's.price = %d',
        ];

        $orders = [
            'id' => 's.id',
            'name' => 's.name',
            'price' => 's.price',
        ];

        foreach ($args as $key => $value) {
            if (isset($filters[$key])) {
                $sql .= $this->wpdb->prepare(
                    " AND {$filters[$key]}",
                    $value
                );
            }
        }

        if (isset($orders[$orderBy])) {
            $sql .= " ORDER BY {$orders[$orderBy]} {$order}";
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

    protected function getTotalCount()
    {
        global $wpdb;
        $sql = "SELECT COUNT(id) FROM {$wpdb->prefix}git_locations";
        $results = $wpdb->get_var($sql);
        if ($results !== null) {
            return (int) $results;
        }
        return 0;
    }
}