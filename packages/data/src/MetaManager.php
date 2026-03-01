<?php
namespace CentralBooking\Data;

/**
 * @internal description
 */
final class MetaManager
{
    public const ZONE = 'location';
    public const ROUTE = 'route';
    public const COUPON = 'coupon';
    public const TICKET = 'ticket';
    public const SERVICE = 'service';
    public const SETTING = 'setting';
    public const LOCATION = 'location';
    public const OPERATOR = 'operator';
    public const TRANSPORT = 'transport';
    public const PASSENGER = 'passenger';

    private static array $cache = [];

    public static function getMeta(string $meta_type, int $meta_id, string $meta_key)
    {
        global $wpdb;

        $cache_key = "{$meta_type}_{$meta_id}_{$meta_key}";

        if (isset(self::$cache[$cache_key])) {
            return self::$cache[$cache_key];
        }

        $table_name = $wpdb->prefix . 'git_meta';

        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT meta_value FROM $table_name WHERE meta_type = %s AND meta_id = %d AND meta_key = %s",
                $meta_type,
                $meta_id,
                $meta_key
            )
        );

        if ($result !== null) {
            $result_parsed = self::unserialize($result);
            self::$cache[$cache_key] = $result_parsed;
            return $result_parsed;
        }

        return null;
    }

    public static function getMapMeta(string $meta_type, int $meta_id, string $meta_key)
    {
        $references = explode('.', $meta_key);
        $map = self::getMeta($meta_type, $meta_id, $references[0]) ?? null;
        foreach (array_slice($references, 1) as $ref) {
            if (is_array($map) && isset($map[$ref])) {
                $map = $map[$ref];
            } else {
                return null;
            }
        }
        return $map;
    }

    public static function getMetadata(string $meta_type, int $meta_id)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'git_meta';

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT meta_key, meta_value FROM $table_name WHERE meta_type = %s AND meta_id = %d",
                $meta_type,
                $meta_id,
            )
        );

        $metadata = [];

        foreach ($results as $meta) {
            self::$cache["{$meta_type}_{$meta_id}_{$meta->meta_key}"] = git_unserialize($meta->meta_value);
            $metadata[$meta->meta_key] = self::$cache["{$meta_type}_{$meta_id}_{$meta->meta_key}"];
        }

        return $metadata;
    }

    /**
     * Saves multiple metadata values for an entity
     * 
     * Allows saving multiple metadata at once using an associative array.
     * Internally calls set_meta() for each key-value pair.
     * 
     * @param string $meta_type The entity type (e.g., 'ticket', 'transport')
     * @param int    $meta_id   The entity ID
     * @param array  $metadata  Associative array [meta_key => meta_value]
     * 
     * @return void
     * 
     * @example
     * MetaManager::set_metadata('transport', 123, [
     *     'capacity' => 45,
     *     'features' => ['wifi', 'ac', 'bathroom'],
     *     'status' => 'active',
     *     'last_maintenance' => '2024-01-15'
     * ]);
     * 
     * @see MetaManager::setMeta() For saving individual metadata
     */
    public static function setMetadata(string $meta_type, int $meta_id, array $metadata)
    {
        foreach ($metadata as $key => $value) {
            self::setMeta($meta_type, $meta_id, $key, $value);
        }
    }

    public static function setMeta(string $meta_type, int $meta_id, string $meta_key, mixed $meta_value)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'git_meta';
        $cache_key = "{$meta_type}_{$meta_id}_{$meta_key}";

        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id FROM $table_name WHERE meta_type = %s AND meta_id = %d AND meta_key = %s",
                $meta_type,
                $meta_id,
                $meta_key
            )
        );

        if ($result === null) {
            $wpdb->insert($table_name, [
                'meta_type' => $meta_type,
                'meta_id' => $meta_id,
                'meta_key' => $meta_key,
                'meta_value' => self::serialize($meta_value)
            ], [
                '%s', // meta_type
                '%d', // meta_id
                '%s', // meta_key
                '%s'  // meta_value
            ]);
        } else {
            $wpdb->update(
                $table_name,
                ['meta_value' => self::serialize($meta_value)],
                ['id' => $result->id],
                ['%s'], // meta_value format
                ['%d']  // id format
            );
        }

        // Update cache
        self::$cache[$cache_key] = $meta_value;
    }

    public static function removeMeta(string $meta_type, int $meta_id, string $meta_key)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'git_meta';

        $wpdb->delete(
            $table_name,
            [
                'meta_type' => $meta_type,
                'meta_id' => $meta_id,
                'meta_key' => $meta_key
            ],
            [
                '%s',
                '%d',
                '%s'
            ]
        );

        // Remove from cache
        $cache_key = "{$meta_type}_{$meta_id}_{$meta_key}";
        unset(self::$cache[$cache_key]);
    }

    private static function serialize(mixed $data)
    {
        if (is_string($data)) {
            return $data;
        }

        if (is_bool($data)) {
            return $data ? 'true' : 'false';
        }

        if (is_null($data)) {
            return 'null';
        }

        if (is_scalar($data)) {
            return (string) $data;
        }

        if (is_array($data)) {
            $arrayString = json_encode($data);
            if (is_bool($arrayString)) {
                return '[]';
            }
            return $arrayString;
        }

        return serialize($data);
    }

    private static function unserialize(string $value)
    {
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        $unserialized = @unserialize($value);
        if ($unserialized !== false) {
            return $unserialized;
        }

        if ($value === 'true')
            return true;
        if ($value === 'false')
            return false;
        if ($value === 'null')
            return null;
        if (is_numeric($value)) {
            return strpos($value, '.') !== false ? (float) $value : (int) $value;
        }

        return $value;
    }
}