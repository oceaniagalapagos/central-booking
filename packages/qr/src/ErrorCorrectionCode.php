<?php
namespace CentralBooking\QR;

enum ErrorCorrectionCode
{
    case LOW;
    case MEDIUM;
    case QUARTILE;
    case HIGH;

    public function label()
    {
        return match ($this) {
            ErrorCorrectionCode::LOW => 'Baja (7%)',
            ErrorCorrectionCode::HIGH => 'Alta (30%)',
            ErrorCorrectionCode::MEDIUM => 'Media (15%)',
            ErrorCorrectionCode::QUARTILE => 'Cuartil (25%)',
            default => $this->name
        };
    }

    public function slug()
    {
        return match ($this) {
            ErrorCorrectionCode::LOW => 'low',
            ErrorCorrectionCode::HIGH => 'high',
            ErrorCorrectionCode::MEDIUM => 'medium',
            ErrorCorrectionCode::QUARTILE => 'quartile',
            default => $this->name
        };
    }

    public static function fromSlug(string $slug)
    {
        return match ($slug) {
            'low' => ErrorCorrectionCode::LOW,
            'high' => ErrorCorrectionCode::HIGH,
            'medium' => ErrorCorrectionCode::MEDIUM,
            'quartile' => ErrorCorrectionCode::QUARTILE,
            default => null
        };
    }
}