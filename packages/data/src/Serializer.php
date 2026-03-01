<?php
namespace CentralBooking\Data;

final class Serializer
{
    private function __construct()
    {
    }

    public static function serialize(mixed $data)
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

    public static function unserialize(string $value): mixed
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
