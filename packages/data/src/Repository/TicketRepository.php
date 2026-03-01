<?php
namespace CentralBooking\Data\Repository;

use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\Data\MetaManager;
use CentralBooking\Data\ORM\ORMInterface;
use CentralBooking\Data\Ticket;
use wpdb;

class TicketRepository
{
    public function __construct(
        private readonly wpdb $wpdb
    ) {
    }

    private function formatTable(string $table)
    {
        return $this->wpdb->prefix . $table;
    }

    /**
     * @param Ticket $ticket
     * @return Ticket
     */
    public function save(Ticket $ticket)
    {
        $data = [
            'status' => $ticket->status->slug(),
            'flexible' => $ticket->flexible ? 1 : 0,
            'total_amount' => $ticket->total_amount,
        ];
        $format = ['%s', '%d', '%d'];

        if ($ticket->getOrder() !== null) {
            $data['id_order'] = $ticket->getOrder()->get_id();
            $format[] = '%d';
        }

        if ($ticket->getCoupon() !== null) {
            $data['id_coupon'] = $ticket->getCoupon()->ID;
            $format[] = '%d';
        }

        if (git_user_logged_in()) {
            $data['id_client'] = get_current_user_id();
            $format[] = '%d';
        }

        if ($this->exists($ticket->id)) {
            $this->wpdb->update(
                $this->formatTable(DatabaseTable::TABLE_TICKETS->value),
                $data,
                ['id' => $ticket->id],
                $format,
                ['%d']
            );
        } else {
            $this->wpdb->insert(
                $this->formatTable(DatabaseTable::TABLE_TICKETS->value),
                $data,
                $format
            );
            $ticket->id = $this->wpdb->insert_id;
        }

        $ticket->saveMeta();

        return $ticket;
    }

    public function exists(int $id): bool
    {
        $tableName = $this->formatTable(DatabaseTable::TABLE_TICKETS->value);
        $sql = "SELECT COUNT(id) from {$tableName} WHERE id = %d";
        $result = $this->wpdb->get_var(
            $this->wpdb->prepare($sql, $id)
        );
        return $result > 0;
    }

    /**
     * @param ORMInterface<Ticket> $orm
     * @param array $args
     * @param string $orderBy
     * @param string $order
     * @param int $limit
     * @param int $offset
     * @return ResultSetInterface<Ticket>
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

    /**
     * @param ORMInterface<Ticket> $orm
     * @param int $id
     * @return Ticket|null
     */
    public function findById(ORMInterface $orm, int $id): ?Ticket
    {
        return $this->findFirst($orm, ['id' => $id]);
    }

    /**
     * @param ORMInterface<Ticket> $orm
     * @param array $args
     * @return Ticket|null
     */
    public function findFirst(ORMInterface $orm, array $args = [])
    {
        $results = $this->find($orm, $args, limit: 1);
        if ($results->hasItems()) {
            return $results->getItems()[0];
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
        $postTable = $this->formatTable('posts');
        $postmetaTable = $this->formatTable('postmeta');
        $metaTable = $this->formatTable(DatabaseTable::TABLE_META->value);
        $ticketTable = $this->formatTable(DatabaseTable::TABLE_TICKETS->value);
        $passsengerTable = $this->formatTable(DatabaseTable::TABLE_PASSENGERS->value);

        $sql = "SELECT t.* FROM {$ticketTable} t
        LEFT JOIN {$metaTable} tm ON (tm.meta_id = t.id AND tm.meta_type = %s)
        LEFT JOIN {$postTable} o ON o.id = t.id_order
        LEFT JOIN {$postTable} c ON c.id = t.id_coupon
        LEFT JOIN {$postmetaTable} om ON om.post_id = o.id
        WHERE 1 = 1";
        // LEFT JOIN {$passsengerTable} p ON p.id_ticket = t.id

        $sql = $this->wpdb->prepare($sql, MetaManager::TICKET);

        $filters = [
            'id' => 't.id = %d',
            'id_client' => 't.id_client = %d',
            'id_passenger' => 'p.id = %d',
            'id_order' => 'o.id = %d',
            'status_not' => 't.status != %s',
            'status' => 't.status = %s',
            'flexible' => 't.flexible = %d',
            'name_buyer' => 't.name_buyer = %s',
            'code_coupon' => "c.post_title = %s",
            'total_amount' => 't.total_amount = %d',
            'date_creation' => 'DATE(o.post_date_gmt) = %s',
            'id_coupon' => "c.post_type = 'shop_coupon' AND c.id = %d",
            'coupon_code' => "o.post_type = 'shop_coupon' AND om.meta_value = %s",
            'phone_buyer' => "om.meta_key = 'billing_phone' AND om.meta_value = %s",
        ];

        if (isset($args['status'])) {
            if ($args['status'] instanceof TicketStatus) {
                $args['status'] = $args['status']->slug();
            } elseif (is_array($args['status'])) {
                $placeholder = array_fill(0, count($args['status']), '$s');
                $placeholderJoin = implode(', ', $placeholder);
                $filters['status'] = "t.status IN ({$placeholderJoin})";
            }
        }

        if (isset($args['status_not'])) {
            if ($args['status_not'] instanceof TicketStatus) {
                $args['status_not'] = $args['status_not']->slug();
            } elseif (is_array($args['status_not'])) {
                $placeholder = array_fill(0, count($args['status_not']), '$s');
                $placeholderJoin = implode(', ', $placeholder);
                $filters['status_not'] = "t.status NOT IN ({$placeholderJoin})";
            }
        }

        if (!isset($args['date_creation'])) {
            if (isset($args['date_creation_from'], $args['date_creation_to'])) {
                $date_start = $args['date_creation_from'];
                $date_end = $args['date_creation_to'];
                $filters['date_creation_between'] = 'o.post_date_gmt BETWEEN %s AND %s';
                $args['date_creation_between'] = [$date_start, $date_end];
                unset($args['date_creation_from'], $args['date_creation_to']);
            } else {
                $filters['date_creation_from'] = 'o.post_date_gmt >= %s';
                $filters['date_creation_to'] = 'o.post_date_gmt <= %s';
            }
        }

        if (isset($args['flexible'])) {
            $args['flexible'] = $args['flexible'] === 'true' || $args['flexible'] === true ? 1 : 0;
        }

        $orders = [
            'price' => 't.total_amount',
            'status' => 't.status',
            'flexible' => 't.flexible',
            'passenger' => 't.passenger',
            'name_buyer' => "om.meta_key",
            'code_coupon' => "om.meta_key",
            'date_creation' => 'o.post_date',
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
        $sql = "SELECT COUNT(DISTINCT t.id) {$sql}";
        $results = $wpdb->get_var($sql);
        if ($results !== null) {
            return (int) $results;
        }
        return 0;
    }
}
