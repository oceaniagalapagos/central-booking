<?php
namespace CentralBooking\Utils\Actions;

use CentralBooking\Data\Operator;
use CentralBooking\Data\Transport;
use WP_Post;

final class InvoiceInfoPagination
{
    public int $total_items = 0;
    public int $total_pages = 0;
    public int $current_page = 1;
    public int $items_per_page = 10;

    public function __construct(
        private readonly Operator $operator,
        private readonly string $date_start,
        private readonly string $date_end,
        private readonly ?WP_Post $coupon = null,
    ) {
    }

    public function fetch_tickets()
    {
        return $this->get_tickets_by_sql(true); // Con paginación
    }

    public function fetch_all_tickets()
    {
        return $this->get_tickets_by_sql(false); // Sin paginación
    }

    private function get_coupons()
    {
        if ($this->coupon !== null)
            return [$this->coupon];
        return $this->operator->getCoupons();
    }

    private function get_tickets_by_sql(bool $use_pagination = true)
    {
        $coupons_ids = array_map(
            fn(WP_Post $coupon) => $coupon->ID,
            $this->get_coupons()
        );
        $transports_ids = array_map(
            fn(Transport $transport) => $transport->id,
            $this->operator->getTransports()
        );

        $coupons = implode(',', $coupons_ids);
        $transports = implode(',', $transports_ids);

        global $wpdb;

        $count_sql = "
            SELECT COUNT(DISTINCT t.id) AS total
            FROM
                {$wpdb->prefix}git_tickets t
                LEFT JOIN {$wpdb->prefix}posts p ON p.ID = t.id_order
                LEFT JOIN {$wpdb->prefix}git_passengers pas ON pas.id_ticket = t.id
                LEFT JOIN {$wpdb->prefix}git_transports tr ON tr.id = pas.id_transport
            WHERE
                (t.id_coupon IN ({$coupons}) __or_null__)
                AND p.post_date >= %s
                AND p.post_date <= %s
                AND pas.id_transport IN ({$transports})
        ";

        if ($this->coupon === null) {
            $count_sql = str_replace('__or_null__', 'OR t.id_coupon IS NULL', $count_sql);
        } else {
            $count_sql = str_replace('__or_null__', '', $count_sql);
        }

        $count_params = [$this->date_start, $this->date_end];

        $this->total_items = (int) $wpdb->get_var(
            $wpdb->prepare($count_sql, $count_params)
        );

        $should_paginate = $use_pagination && $this->items_per_page > 0;

        if ($should_paginate) {
            $this->total_pages = ceil($this->total_items / $this->items_per_page);
        } else {
            $this->total_pages = 1;
        }

        if ($this->total_items === 0) {
            return [];
        }

        $data_sql = "
            SELECT
                DISTINCT t.id
            FROM
                {$wpdb->prefix}git_tickets t
                LEFT JOIN {$wpdb->prefix}posts p ON p.ID = t.id_order
                LEFT JOIN {$wpdb->prefix}git_passengers pas ON pas.id_ticket = t.id
                LEFT JOIN {$wpdb->prefix}git_transports tr ON tr.id = pas.id_transport
            WHERE
                (t.id_coupon IN ({$coupons}) __or_null__)
                AND p.post_date >= %s
                AND p.post_date <= %s
                AND pas.id_transport IN ({$transports})
        ";

        if ($this->coupon === null) {
            $data_sql = str_replace('__or_null__', 'OR t.id_coupon IS NULL', $data_sql);
        } else {
            $data_sql = str_replace('__or_null__', '', $data_sql);
        }

        $data_params = [$this->date_start, $this->date_end];

        if ($should_paginate) {
            $offset = ($this->current_page - 1) * $this->items_per_page;
            $data_sql .= " LIMIT %d OFFSET %d";
            $data_params = array_merge($data_params, [$this->items_per_page, $offset]);
        }

        $ticket_ids = $wpdb->get_col($wpdb->prepare($data_sql, $data_params));

        // echo $wpdb->prepare($data_sql, $data_params);

        return $this->convert_ids_to_tickets($ticket_ids);
    }

    private function convert_ids_to_tickets(array $ticket_ids)
    {
        $tickets = [];

        foreach ($ticket_ids as $ticket_id) {
            $ticket = git_ticket_by_id($ticket_id);
            if ($ticket) {
                $tickets[] = $ticket;
            }
        }

        return $tickets;
    }
}