<?php
namespace CentralBooking\Data\Repository;

use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\Data\ORM\ORMInterface;
use CentralBooking\Data\Passenger;
use wpdb;

class PassengerRepository
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

    public function save(Passenger $passenger)
    {
        $data = [
            'name' => $passenger->name,
            'nationality' => $passenger->nationality,
            'type_document' => $passenger->typeDocument,
            'data_document' => $passenger->dataDocument,
            'birthday' => $passenger->getBirthday()->format('Y-m-d'),
            'served' => $passenger->served ? 1 : 0,
            'approved' => $passenger->approved ? 1 : 0,
            'date_trip' => $passenger->getDateTrip()->format('Y-m-d'),
            'id_route' => $passenger->getRoute()->id,
            'id_ticket' => $passenger->getTicket()->id,
            'id_transport' => $passenger->getTransport()->id,
        ];
        $formats = [
            '%s',
            '%s',
            '%s',
            '%s',
            '%s',
            '%d',
            '%d',
            '%s',
            '%d',
            '%d',
            '%d',
        ];
        if ($this->exists($passenger->id)) {
            $this->wpdb->update(
                $this->formatTable(DatabaseTable::TABLE_PASSENGERS->value),
                $data,
                ['id' => $passenger->id],
                $formats,
                ['%d']
            );
        } else {
            $this->wpdb->insert(
                $this->formatTable(DatabaseTable::TABLE_PASSENGERS->value),
                $data,
                $formats
            );
            $passenger->id = $this->wpdb->insert_id;
        }

        return $passenger;
    }

    private function exists(int $id): bool
    {
        $tableName = $this->formatTable(DatabaseTable::TABLE_PASSENGERS->value);
        $sql = "SELECT COUNT(id) from {$tableName} WHERE id = %d";
        $result = $this->wpdb->get_var(
            $this->wpdb->prepare($sql, $id)
        );
        return $result > 0;
    }
    /**
     * @param ORMInterface<Passenger> $orm
     * @param array $args
     * @param string $orderBy
     * @param string $order
     * @param int $limit
     * @param int $offset
     * @return ResultSetInterface<Passenger>
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
        $postTable = $this->formatTable('posts');
        $postmetaTable = $this->formatTable('postmeta');
        $routeTable = $this->formatTable(DatabaseTable::TABLE_ROUTES->value);
        $ticketTable = $this->formatTable(DatabaseTable::TABLE_TICKETS->value);
        $transportTable = $this->formatTable(DatabaseTable::TABLE_TRANSPORTS->value);
        $passsengerTable = $this->formatTable(DatabaseTable::TABLE_PASSENGERS->value);

        $sql = "SELECT p.*
        FROM {$passsengerTable} p
        LEFT JOIN {$routeTable} r ON r.id = p.id_route
        LEFT JOIN {$ticketTable} t ON t.id = p.id_ticket
        LEFT JOIN {$transportTable} tr ON tr.id = p.id_transport
        LEFT JOIN {$postTable} o ON o.id = t.id_order
        LEFT JOIN {$postmetaTable} om ON om.post_id = o.id
        WHERE 1=1";

        $filters = [
            'id' => 'p.id = %d',
            'id_ticket' => 't.id = %d',
            'id_origin' => 'r.id_origin = %d',
            'id_destiny' => 'r.id_destiny = %d',
            'ticket_status' => 't.status = %s',
            'ticket_status_not' => 't.status != %s',
            'ticket_flexible' => 't.flexible = %d',
            'id_route' => 'r.id = %d',
            'departure_time' => 'r.departure_time = %s',
            'id_transport' => 'tr.id = %d',
            'name' => 'p.name LIKE %s',
            'nationality' => 'p.nationality = %s',
            'type_document' => 'p.type_document = %s',
            'data_document' => 'p.data_document LIKE %s',
            'type' => 'p.type = %s',
            'served' => 'p.served = %d',
            'date_trip' => 'p.date_trip = %s',
            'date_birth' => 'p.date_birth = %s',
            'approved' => 'p.approved = %d',
        ];

        if (isset($args['served'])) {
            $args['served'] = $args['served'] === 'true' || $args['served'] === true ? 1 : 0;
        }

        if (isset($args['approved'])) {
            $args['approved'] = $args['approved'] === 'true' || $args['approved'] === true ? 1 : 0;
        }

        if (isset($args['ticket_status'])) {
            $args['ticket_status'] = $args['ticket_status'] instanceof TicketStatus ? $args['ticket_status']->slug() : $args['ticket_status'];
        }

        if (isset($args['ticket_status_not'])) {
            $args['ticket_status_not'] = $args['ticket_status_not'] instanceof TicketStatus ? $args['ticket_status_not']->slug() : $args['ticket_status_not'];
        }

        if (isset($args['date_trip']) === false) {
            if (isset($args['date_trip_from']) && isset($args['date_trip_to'])) {
                $filters['date_trip_between'] = 'p.date_trip BETWEEN %s AND %s';
                $args['date_trip_between'] = [$args['date_trip_from'], $args['date_trip_to']];
                unset($args['date_trip_from'], $args['date_trip_to']);
            }
            if (isset($args['date_trip_from']) && !isset($args['date_trip_to'])) {
                $filters['date_trip_from'] = 'p.date_trip >= %s';
            }
            if (!isset($args['date_trip_from']) && isset($args['date_trip_to'])) {
                $filters['date_trip_to'] = 'p.date_trip <= %s';
            }
        }

        $orders = [
            'id' => 'p.id',
            'ticket_status' => 't.status',
            'id_ticket' => 'p.id_ticket',
            'id_route' => 'p.id_route',
            'id_transport' => 'p.id_transport',
            'name' => 'p.name',
            'nationality' => 'p.nationality',
            'type_document' => 'p.type_document',
            'data_document' => 'p.data_document',
            'type' => 'p.type',
            'approved' => 'p.approved',
            'served' => 'p.served',
            'date_trip' => 'p.date_trip',
            'date_birth' => 'p.date_birth',
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
        $sql = "SELECT COUNT(DISTINCT p.id) {$sql}";
        $results = $wpdb->get_var($sql);
        if ($results !== null) {
            return (int) $results;
        }
        return 0;
    }
}
