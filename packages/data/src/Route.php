<?php
namespace CentralBooking\Data;

use CentralBooking\Data\Constants\TypeOperation;
use CentralBooking\Data\Repository\LazyLoader;

/**
 * Class Route
 * 
 * Represents a travel route with origin, destination, transport options and scheduling information.
 * Implements lazy loading for related entities like locations and transports.
 * 
 * @package CentralBooking\Data
 * @author Central Booking System
 * @version 1.0.0
 * @since 1.0.0
 */
class Route
{
    /**
     * Unique identifier for the route
     * 
     * @var int
     */
    public int $id = 0;

    /**
     * Type of operation for the route (marine, terrestrial, etc.)
     * 
     * @var TypeOperation
     */
    public TypeOperation $type = TypeOperation::MARINE;

    /**
     * Departure time for the route
     * 
     * @var Time
     */
    public Time $departureTime;

    /**
     * Arrival time for the route
     * 
     * @var Time
     */
    public Time $arrivalTime;

    /**
     * Collection of transport options for this route
     * 
     * @var array<Transport>
     */
    private array $transports;

    /**
     * Origin location of the route
     * 
     * @var Location
     */
    private Location $origin;

    /**
     * Destination location of the route
     * 
     * @var Location
     */
    private Location $destiny;

    /**
     * Metadata associated with the route
     * 
     * @var array<string, mixed>
     */
    private array $metadata = [];

    /**
     * Flag to track if origin location has been loaded
     * 
     * @var bool
     */
    private bool $originLoaded = false;

    /**
     * Flag to track if destination location has been loaded
     * 
     * @var bool
     */
    private bool $destinyLoaded = false;

    /**
     * Flag to track if transports have been loaded
     * 
     * @var bool
     */
    private bool $transportsLoaded = false;

    /**
     * Get metadata value by key
     * 
     * Retrieves metadata value for the given key. If the value is not cached,
     * it will be loaded from the MetaManager and cached for future use.
     * 
     * @param string $key The metadata key to retrieve
     * @return mixed The metadata value
     */
    public function getMeta(string $key)
    {
        if (isset($this->metadata[$key]) === false) {
            $value = MetaManager::getMeta(
                MetaManager::ROUTE,
                $this->id,
                $key
            );
            $this->metadata[$key] = $value;
        }
        return $this->metadata[$key];
    }

    /**
     * Set metadata value for a key
     * 
     * Stores a metadata value in the local cache. Call saveMeta() to persist changes.
     * 
     * @param string $key The metadata key
     * @param mixed $value The metadata value to store
     * @return void
     */
    public function setMeta(string $key, mixed $value)
    {
        $this->metadata[$key] = $value;
    }

    /**
     * Save all metadata changes to persistent storage
     * 
     * Persists all cached metadata changes to the database through MetaManager.
     * 
     * @return void
     */
    public function saveMeta()
    {
        MetaManager::setMetadata(
            MetaManager::ROUTE,
            $this->id,
            $this->metadata
        );
    }

    /**
     * Get the origin location of the route
     * 
     * Uses lazy loading to fetch the origin location from the database
     * only when needed. Subsequent calls return the cached value.
     * 
     * @return Location The origin location
     */
    public function getOrigin()
    {
        if ($this->originLoaded === false) {
            $this->origin = LazyLoader::loadOriginByRoute($this);
            $this->originLoaded = true;
        }
        return $this->origin;
    }

    /**
     * Set the origin location of the route
     * 
     * Sets the origin location and marks it as loaded to avoid
     * unnecessary lazy loading in future calls.
     * 
     * @param Location $origin The origin location to set
     * @return void
     */
    public function setOrigin(Location $origin)
    {
        $this->origin = $origin;
        $this->originLoaded = true;
    }

    /**
     * Get the destination location of the route
     * 
     * Uses lazy loading to fetch the destination location from the database
     * only when needed. Subsequent calls return the cached value.
     * 
     * @return Location The destination location
     */
    public function getDestiny()
    {
        if ($this->destinyLoaded === false) {
            $this->destiny = LazyLoader::loadDestinyByRoute($this);
            $this->destinyLoaded = true;
        }
        return $this->destiny;
    }

    /**
     * Set the destination location of the route
     * 
     * Sets the destination location and marks it as loaded to avoid
     * unnecessary lazy loading in future calls.
     * 
     * @param Location $destiny The destination location to set
     * @return void
     */
    public function setDestiny(Location $destiny)
    {
        $this->destiny = $destiny;
        $this->destinyLoaded = true;
    }

    /**
     * Get the departure time of the route
     * 
     * Returns the departure time or creates a new Time instance if not set.
     * 
     * @return Time The departure time
     */
    public function getDepartureTime()
    {
        return $this->departureTime ?? new Time;
    }

    /**
     * Set the departure time of the route
     * 
     * @param Time $departureTime The departure time to set
     * @return void
     */
    public function setDepartureTime(Time $departureTime)
    {
        $this->departureTime = $departureTime;
    }

    /**
     * Get the arrival time of the route
     * 
     * Returns the arrival time or creates a new Time instance if not set.
     * 
     * @return Time The arrival time
     */
    public function getArrivalTime()
    {
        return $this->arrivalTime ?? new Time;
    }

    /**
     * Set the arrival time of the route
     * 
     * @param Time $arrivalTime The arrival time to set
     * @return void
     */
    public function setArrivalTime(Time $arrivalTime)
    {
        $this->arrivalTime = $arrivalTime;
    }

    /**
     * Get all transport options for the route
     * 
     * Uses lazy loading to fetch transport options from the database
     * only when needed. Subsequent calls return the cached value.
     * 
     * @return array<Transport> Array of transport options
     */
    public function getTransports()
    {
        if ($this->transportsLoaded === false) {
            $this->transports = LazyLoader::loadTransportsByRoute($this);
            $this->transportsLoaded = true;
        }
        return $this->transports;
    }

    /**
     * @param array<Transport> $transports
     * @return void
     */
    public function setTransports(array $transports)
    {
        $this->transports = $transports;
        $this->transportsLoaded = true;
    }

    /**
     * Save the route to persistent storage
     * 
     * Persists the route entity to the database using the git_route_save function.
     * 
     * @return bool True if the route was saved successfully, false otherwise
     */
    public function save()
    {
        $saved = git_route_save($this);
        return $saved !== null;
    }
}
