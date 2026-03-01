<?php
namespace CentralBooking\Data;

use CentralBooking\Data\Repository\LazyLoader;

/**
 * Class Zone
 *
 * Representa una zona geográfica o lógica dentro del sistema CentralBooking.
 * Cada zona puede contener múltiples ubicaciones (Location).
 *
 * @package CentralBooking\Data
 */
final class Zone
{
    /**
     * ID único de la zona.
     *
     * @var int
     */
    public int $id = 0;

    /**
     * Nombre descriptivo de la zona.
     *
     * @var string
     */
    public string $name = '';

    /**
     * Lista de ubicaciones asociadas a esta zona.
     *
     * @var Location[]
     */
    private array $locations = [];

    private array $metadata = [];
    private bool $locationsLoaded = false;

    public function getMeta(string $key)
    {
        if (isset($this->metadata[$key]) === false) {
            $value = MetaManager::getMeta(
                MetaManager::ZONE,
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
            MetaManager::ZONE,
            $this->id,
            $this->metadata
        );
    }

    /**
     * Obtiene las ubicaciones asociadas a esta zona.
     * Si no están cargadas, se obtienen mediante LazyLoader.
     *
     * @return Location[]
     */
    public function getLocations()
    {
        if ($this->locationsLoaded === false) {
            $this->locations = LazyLoader::loadLocationsByZone($this);
            $this->locationsLoaded = true;
        }
        return $this->locations;
    }

    /**
     * Asigna una lista de ubicaciones a esta zona.
     *
     * @param Location[] $locations
     * @return void
     */
    public function setLocations(array $locations)
    {
        $this->locations = $locations;
    }

    public function save()
    {
        return git_zone_save($this) !== null;
    }
}