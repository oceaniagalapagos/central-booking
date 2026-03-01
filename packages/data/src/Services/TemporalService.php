<?php
namespace CentralBooking\Data\Services;

use CentralBooking\Data\MetaManager;

/**
 * Temporal storage service for managing time-based data storage with automatic expiration.
 * 
 * This service provides a simple interface for storing data temporarily with automatic
 * cleanup based on expiration times. Data is stored using the MetaManager system.
 * 
 * @package CentralBooking\Data\Services
 * @since 1.0.0
 */
final class TemporalService
{
    /** Meta key used for storing temporal data */
    private const META_KEY = "temporal_storage";

    /**
     * Store data temporarily with an expiration time.
     * 
     * @param mixed $data The data to store (can be any serializable type)
     * @param string $key Unique identifier for the stored data
     * @param int $expiration Time in seconds until expiration (default: 30 seconds)
     * 
     * @return void
     * 
     * @since 1.0.0
     */
    public function write(string $key, mixed $data, int $expiration = 30)
    {
        if (empty($key) || $expiration <= 0) {
            return;
        }
        MetaManager::setMeta(
            self::META_KEY,
            0,
            $key,
            [
                'data' => $data,
                'expiration' => time() + $expiration,
            ]
        );
    }

    /**
     * Retrieve stored data by key.
     * 
     * If the data has expired, it will be automatically removed and null returned.
     * Optionally, data can be deleted immediately after reading.
     * 
     * @param string $key Unique identifier for the stored data
     * @param bool $deleteAfterRead Whether to delete the data after reading (default: false)
     * 
     * @return mixed|null The stored data or null if not found/expired
     * 
     * @since 1.0.0
     */
    public function read(string $key, bool $deleteAfterRead = false)
    {
        $data = MetaManager::getMeta(self::META_KEY, 0, $key);
        if ($data === null) {
            return null;
        }
        if (time() > $data['expiration']) {
            $this->erase($key);
            return null;
        }
        if ($deleteAfterRead) {
            $this->erase($key);
        }
        return $data['data'];
    }

    /**
     * Permanently remove stored data by key.
     * 
     * @param string $key Unique identifier for the stored data to remove
     * 
     * @return void
     * 
     * @since 1.0.0
     */
    public function erase(string $key)
    {
        MetaManager::removeMeta(
            self::META_KEY,
            0,
            $key
        );
    }
}