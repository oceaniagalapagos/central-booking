<?php
namespace CentralBooking\Data\Services;

class LogService
{
    public int $id;
    public ?int $id_source;
    public string $level;
    public string $source;
    public string $message;
    public string $time_stamp;

    public function __construct()
    {
        $this->time_stamp = date('Y-m-d H:i:s');
    }
    /**
     * Creates a new log entry in the database.
     *
     * @param string $source The source of the log (e.g., 'tickets', 'zones').
     * @param ?int $id_source The ID of the source entity (e.g., ticket ID).
     * @param string $message The log message.
     * @param string $level The log level (e.g., 'info', 'error').
     * @return int|false The number of rows affected or false on error.
     */
    public static function create_git_log(string $source, ?int $id_source, string $message, string $level)
    {
        global $wpdb;

        $data = [
            'level' => $level,
            'source' => $source,
            'message' => $message,
            'timestamp' => current_time('mysql'),
        ];

        $format = ['%s', '%s', '%s', '%s'];

        if (!is_null($id_source)) {
            $data['id_source'] = $id_source;
            $format[] = '%d';
        }

        return $wpdb->insert("{$wpdb->prefix}git_log", $data, $format);
    }
    /**
     * Retrieves git logs based on the provided filters.
     *
     * @param string $level The log level to filter by (optional).
     * @param string $source The source of the log to filter by (optional).
     * @param string $date_from Start date for filtering logs (optional).
     * @param string $date_to End date for filtering logs (optional).
     * @param int $id_source The ID of the source entity to filter by (optional).
     * @param int $page The page number for pagination (default is 1).
     * @param int $limit The number of logs to retrieve per page (default is -1).
     *
     * @return LogService[] An array of GIT_LOG objects.
     */
    public static function recover_git_log(
        string $level = '',
        string $source = '',
        string $date_from = '',
        string $date_to = '',
        int $id_source = 0,
        int $page = 1,
        int $limit = -1
    ) {
        global $wpdb;

        $table_name = "{$wpdb->prefix}git_log";
        $sql = "SELECT * FROM $table_name WHERE 1=1";

        if (!empty($source)) {
            $sql .= $wpdb->prepare(" AND source = %s", $source);
        }

        if (!empty($id_source)) {
            $sql .= $wpdb->prepare(" AND id_source = %d", $id_source);
        }

        if (!empty($level)) {
            $sql .= $wpdb->prepare(" AND level = %s", $level);
        }

        if (!empty($date_from) && !empty($date_to)) {
            $sql .= $wpdb->prepare(" AND time_stamp BETWEEN %s AND %s", $date_from, $date_to);
        } elseif (!empty($date_from)) {
            $sql .= $wpdb->prepare(" AND time_stamp >= %s", $date_from);
        } elseif (!empty($date_to)) {
            $sql .= $wpdb->prepare(" AND time_stamp <= %s", $date_to);
        }

        $sql .= " ORDER BY timestamp DESC";

        if ($limit > 0) {
            $offset = ($page - 1) * $limit;
            $sql .= $wpdb->prepare(" LIMIT %d OFFSET %d", $limit, $offset);
        }

        return array_map(function ($result) {
            $log = new self();
            $log->id = $result->id;
            $log->id_source = $result->id_source;
            $log->level = $result->level;
            $log->source = $result->source;
            $log->message = $result->message;
            $log->time_stamp = $result->timestamp;
            return $log;
        }, $wpdb->get_results($sql));
    }

    /**
     * Retrieves git logs with pagination information.
     *
     * @param string $level The log level to filter by (optional).
     * @param string $source The source of the log to filter by (optional).
     * @param string $date_from Start date for filtering logs (optional).
     * @param string $date_to End date for filtering logs (optional).
     * @param int $id_source The ID of the source entity to filter by (optional).
     * @param int $page The page number for pagination (default is 1).
     * @param int $per_page The number of logs to retrieve per page (default is 20).
     *
     * @return array{
     *   logs: LogService[],
     *   pagination: array{
     *     current_page: int,
     *     per_page: int,
     *     total_items: int,
     *     total_pages: int,
     *     has_previous: bool,
     *     has_next: bool,
     *     offset: int
     *   }
     * }
     */
    public static function get_logs_with_pagination(
        string $level = '',
        string $source = '',
        string $date_from = '',
        string $date_to = '',
        int $id_source = 0,
        int $page = 1,
        int $per_page = 20
    ): array {
        global $wpdb;

        $table_name = "{$wpdb->prefix}git_log";

        $page = max(1, $page);
        $per_page = max(1, $per_page);

        $where_conditions = [];
        $where_values = [];

        if (!empty($source)) {
            $where_conditions[] = "source = %s";
            $where_values[] = $source;
        }

        if (!empty($id_source)) {
            $where_conditions[] = "id_source = %d";
            $where_values[] = $id_source;
        }

        if (!empty($level)) {
            $where_conditions[] = "level = %s";
            $where_values[] = $level;
        }

        if (!empty($date_from) && !empty($date_to)) {
            $where_conditions[] = "timestamp BETWEEN %s AND %s";
            $where_values[] = $date_from;
            $where_values[] = $date_to;
        } elseif (!empty($date_from)) {
            $where_conditions[] = "timestamp >= %s";
            $where_values[] = $date_from;
        } elseif (!empty($date_to)) {
            $where_conditions[] = "timestamp <= %s";
            $where_values[] = $date_to;
        }

        $where_clause = !empty($where_conditions)
            ? 'WHERE ' . implode(' AND ', $where_conditions)
            : '';

        $count_sql = "SELECT COUNT(*) FROM $table_name $where_clause";
        if (!empty($where_values)) {
            $count_sql = $wpdb->prepare($count_sql, $where_values);
        }
        $total_items = (int) $wpdb->get_var($count_sql);

        $total_pages = ceil($total_items / $per_page);
        $offset = ($page - 1) * $per_page;
        $has_previous = $page > 1;
        $has_next = $page < $total_pages;

        $data_sql = "SELECT * FROM $table_name $where_clause ORDER BY timestamp DESC LIMIT %d OFFSET %d";
        $data_values = array_merge($where_values, [$per_page, $offset]);
        $data_sql = $wpdb->prepare($data_sql, $data_values);

        $results = $wpdb->get_results($data_sql);

        $logs = array_map(function ($result) {
            $log = new self();
            $log->id = (int) $result->id;
            $log->id_source = $result->id_source ? (int) $result->id_source : null;
            $log->level = $result->level;
            $log->source = $result->source;
            $log->message = $result->message;
            $log->time_stamp = $result->timestamp;
            return $log;
        }, $results);

        return [
            'logs' => $logs,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $per_page,
                'total_items' => $total_items,
                'total_pages' => $total_pages,
                'has_previous' => $has_previous,
                'has_next' => $has_next,
                'offset' => $offset,
                'showing_from' => $total_items > 0 ? $offset + 1 : 0,
                'showing_to' => min($offset + $per_page, $total_items)
            ]
        ];
    }
}