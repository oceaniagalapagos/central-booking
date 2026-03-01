<?php
namespace CentralBooking\Implementation\GUI;

use CentralBooking\Data\Zone;

class ZoneSelect
{
    /**
     * @var array<Zone>
     */
    private array $zones;

    public function __construct(private string $name = 'zone')
    {
        $this->zones = git_zones(['order_by' => 'name']) ?? [];
    }

    public function create(bool $multiple = false)
    {
        $selectComponent = $multiple ? git_multiselect_field(['name' => $this->name]) : git_select_field(['name' => $this->name]);

        $selectComponent->addOption('Seleccione...', '');

        foreach ($this->zones as $zone) {
            $selectComponent->addOption($zone->name, $zone->id);
        }

        return $selectComponent;
    }
}
