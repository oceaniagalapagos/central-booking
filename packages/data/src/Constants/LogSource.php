<?php
namespace CentralBooking\Data\Constants;

enum LogSource
{
    case ZONE;
    case ROUTE;
    case SYSTEM;
    case TICKET;
    case SERVICE;
    case OPERATOR;
    case LOCATION;
    case TRANSPORT;
    case PASSENGER;

    public function slug()
    {
        return match ($this) {
            self::ZONE => 'zones',
            self::ROUTE => 'routes',
            self::SYSTEM => 'system',
            self::TICKET => 'tickets',
            self::SERVICE => 'services',
            self::OPERATOR => 'operators',
            self::LOCATION => 'locations',
            self::TRANSPORT => 'transports',
            self::PASSENGER => 'passengers',
            default => $this->name
        };
    }
}