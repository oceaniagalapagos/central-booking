<?php
namespace CentralBooking\Data;

use CentralBooking\Data\Repository\LazyLoader;

final class Service
{
    public int $id = 0;
    public string $name = '';
    public string $icon = '';
    public int $price = 0;

    // Campos privados

    private array $metadata = [];
    private array $transports = [];
    private bool $transportsLoaded = false;

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

    public function getTransports()
    {
        if ($this->transportsLoaded === false) {
            $this->transports = LazyLoader::loadTransportsByService($this);
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

    public function save()
    {
        return git_service_save($this) !== null;
    }
}
