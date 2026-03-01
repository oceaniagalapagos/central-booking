<?php
namespace CentralBooking\Data\Constants;

enum PassengerConstants: string
{
    case KID = 'kid';
    case RPM = 'rpm';
    case STANDARD = 'standard';

    public function display()
    {
        return match ($this) {
            self::KID => 'Niño',
            self::RPM => 'Movilidad Reducida',
            self::STANDARD => 'Estándar',
            default => $this->name,
        };
    }

    public function slug()
    {
        return match ($this) {
            self::KID => 'kid',
            self::RPM => 'rpm',
            self::STANDARD => 'standard',
            default => $this->name,
        };
    }
}
