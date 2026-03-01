<?php
namespace CentralBooking\Data\Repository;

use CentralBooking\Data\Location;
use CentralBooking\Data\MetaManager;
use CentralBooking\Data\ORM\ORMInterface;
use wpdb;

final class LocationRepository
{
    private string $tableLocations = "git_locations";

    /**
     * @param ORMInterface<Location> $orm
     */
    public function __construct(private readonly wpdb $wpdb)
    {
    }

    public function save(Location $location)
    {
        $data = ['name' => $location->name];
        $formats = ['%s'];
        if ($this->exists($location->id)) {
            $this->wpdb->update(
                $this->getTable(),
                $data,
                ['id' => $location->id],
                $formats,
                ['%d']
            );
        } else {
            $this->wpdb->insert(
                $this->getTable(),
                $data,
                $formats
            );
            $location->id = $this->wpdb->insert_id;
            MetaManager::setMeta(
                MetaManager::LOCATION,
                $location->id,
                'type',
                'location'
            );
        }
        MetaManager::setMeta(
            MetaManager::LOCATION,
            $location->id,
            'parent_zone',
            $location->getZone()->id
        );
        $location->saveMeta();
        return $location;
    }

    private function getTable()
    {
        return $this->formatTable($this->tableLocations);
    }

    private function formatTable(string $table)
    {
        return $this->wpdb->prefix . $table;
    }

    private function exists(int $id): bool
    {
        $tableName = $this->getTable();
        $sql = "SELECT COUNT(id) FROM {$tableName} WHERE id = %d";
        $result = $this->wpdb->get_var(
            $this->wpdb->prepare($sql, $id)
        );
        return $result > 0;
    }

    /**
     * @param ORMInterface<Location> $orm
     * @param array $args
     * @param string $orderBy
     * @param string $order
     * @param int $limit
     * @param int $offset
     * @return ResultSetInterface<Location>
     */
    public function find(
        ORMInterface $orm,
        array $args = [],
        string $orderBy = 'id',
        string $order = 'ASC',
        int $limit = -1,
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

    public function findById(
        ORMInterface $orm,
        int $id
    ) {
        return $this->findFirst(
            $orm,
            ['id' => $id]
        );
    }

    /**
     * @param ORMInterface<Location> $orm
     * @param array $args
     * @return Location|null
     */
    public function findFirst(
        ORMInterface $orm,
        array $args = []
    ) {
        $result = $this->find(
            $orm,
            $args,
            limit: 1,
            offset: 0
        );
        if ($result->hasItems()) {
            return $result->getItems()[0];
        }
        return null;
    }

    private function getQuery(
        array $args = [],
        string $orderBy = 'id',
        string $order = 'ASC',
        int $limit = -1,
        int $offset = 0
    ) {
        $metaTable = $this->formatTable('git_meta');
        $locationsTable = $this->formatTable('git_locations');

        $sql = "SELECT l.*
        FROM {$locationsTable} l
        LEFT JOIN {$metaTable} m ON l.id = m.meta_id
        WHERE m.meta_type = %s
        AND m.meta_key = %s
        AND m.meta_value = %s";

        $sql = $this->wpdb->prepare(
            $sql,
            MetaManager::LOCATION,
            'type',
            'location',
        );

        $filters = [
            'id' => 'l.id = %d',
            'name' => 'l.name = %s',
            'id_zone' => 'm.parent_zone = %s',
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

    private function getTotalCount(array $args = []): int
    {
        global $wpdb;
        $sql = $this->getQuery(
            args: $args,
            limit: -1
        );
        $sql = substr($sql, 10);
        $sql = "SELECT COUNT(id) {$sql}";
        git_set_setting('sample', $sql);
        $results = $wpdb->get_var($sql);
        if ($results !== null) {
            return (int) $results;
        }
        return 0;
    }
}
