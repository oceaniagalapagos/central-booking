<?php
namespace CentralBooking\Data\Constants;

enum RouteConstants: string
{
    case BETWEEN_LOCATIONS = 'between_locations';
    case BETWEEN_ZONES = 'between_zones';

    public function label()
    {
        return match ($this) {
            self::BETWEEN_ZONES => 'Entre Zonas',
            self::BETWEEN_LOCATIONS => 'Entre Localidades',
            default => $this->value,
        };
    }
}
