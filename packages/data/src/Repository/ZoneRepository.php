<?php
namespace CentralBooking\Data\Repository;

use CentralBooking\Data\MetaManager;
use CentralBooking\Data\ORM\ORMInterface;
use CentralBooking\Data\Repository\DatabaseTable;
use CentralBooking\Data\Zone;
use wpdb;

class ZoneRepository
{
    private string $table = "git_locations";

    public function __construct(private readonly wpdb $wpdb)
    {
    }

    public function save(Zone $zone)
    {
        $data = ['name' => $zone->name];
        $formats = ['%s'];
        if ($this->exists($zone->id)) {
            $this->wpdb->update(
                $this->formatTable(DatabaseTable::TABLE_LOCATIONS->value),
                $data,
                ['id' => $zone->id],
                $formats,
                ['%d']
            );
        } else {
            $result = $this->wpdb->insert(
                $this->formatTable(DatabaseTable::TABLE_LOCATIONS->value),
                $data,
                $formats
            );
            if (!$result) {
                return null;
            }
            $zone->id = $this->wpdb->insert_id;
            MetaManager::setMeta(
                MetaManager::ZONE,
                $zone->id,
                'type',
                'zone'
            );
        }
        return $zone;
    }

    private function getTable()
    {
        return $this->formatTable($this->table);
    }

    private function formatTable(string $table)
    {
        return $this->wpdb->prefix . $table;
    }

    private function exists(int $id): bool
    {
        $tableName = $this->getTable();
        $sql = "SELECT COUNT(id) from {$tableName} WHERE id = %d";
        $result = $this->wpdb->get_var(
            $this->wpdb->prepare($sql, $id)
        );
        return ((int) $result) > 0;
    }

    /**
     * @param ORMInterface<Zone> $orm
     * @param array $args
     * @param string $orderBy
     * @param string $order
     * @param int $limit
     * @param int $offset
     * @return ResultSetInterface<Zone>
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
     * @param ORMInterface<Zone> $orm
     * @param int $id
     * @return Zone|null
     */
    public function findById(ORMInterface $orm, int $id)
    {
        return $this->findFirst($orm, ['id' => $id]);
    }

    /**
     * @param ORMInterface<Zone> $orm
     * @param array $args
     * @return Zone|null
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
        $metaTable = $this->formatTable(DatabaseTable::TABLE_META->value);
        $locationsTable = $this->formatTable(DatabaseTable::TABLE_LOCATIONS->value);

        $sql = "SELECT l.* FROM {$locationsTable} l LEFT JOIN {$metaTable} m ON l.id = m.meta_id WHERE m.meta_type = %s AND m.meta_key = %s AND m.meta_value = %s";
        $sql = $this->wpdb->prepare(
            $sql,
            MetaManager::ZONE,
            'type',
            'zone',
        );

        $filters = [
            'id' => 'l.id = %d',
            'name' => 'l.name = %s',
        ];

        $orders = [
            'id' => 'l.id',
            'name' => 'l.name',
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
        $locationsTable = $this->formatTable(DatabaseTable::TABLE_LOCATIONS->value);
        $sql = "SELECT COUNT(id) FROM {$locationsTable}";
        $results = $this->wpdb->get_var($sql);
        if ($results !== null) {
            return (int) $results;
        }
        return 0;
    }
}