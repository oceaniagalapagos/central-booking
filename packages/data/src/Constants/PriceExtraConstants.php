<?php
namespace CentralBooking\Data\Constants;

enum PriceExtraConstants: string
{
    case EXTRA = 'extra';
    case FLEXIBLE = 'flexible';
    case TERMS_CONDITIONS = 'terms_conditions';

    public function label()
    {
        return match ($this) {
            self::EXTRA => 'Extra',
            self::FLEXIBLE => 'Flexible',
            self::TERMS_CONDITIONS => 'Términos y condiciones',
            default => $this->value,
        };
    }
}
