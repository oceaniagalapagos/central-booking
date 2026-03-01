<?php
namespace CentralBooking\Data;

use CentralBooking\Data\Repository\LazyLoader;

class Location
{
    public int $id = 0;
    public string $name = '';

    private Zone $zone;
    private bool $zoneLoaded = false;
    private array $metadata = [];

    public function getZone()
    {
        if ($this->zoneLoaded === false) {
            $this->zone = LazyLoader::loadZoneByLocation($this);
            $this->zoneLoaded = true;
        }
        return $this->zone;
    }

    public function setZone(Zone $zone)
    {
        $this->zone = $zone;
        $this->zoneLoaded = true;
    }

    public function getMeta(string $key)
    {
        if (isset($this->metadata[$key]) === false) {
            $value = MetaManager::getMeta(
                MetaManager::LOCATION,
                $this->id,
                $key
            );
            $this->metadata[$key] = $value;
        }
        return $this->metadata[$key];
    }

    public function setMeta(string $key, mixed $value)
    {
        $this->metadata[$key] = $value;
    }

    public function saveMeta()
    {
        MetaManager::setMetadata(
            MetaManager::LOCATION,
            $this->id,
            $this->metadata
        );
    }

    public function save()
    {
        return git_location_save($this) !== null;
    }
}
