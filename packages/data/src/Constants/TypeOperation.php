<?php
namespace CentralBooking\Data\Constants;

use CentralBooking\Admin\Setting\SettingsKeys;

enum TypeOperation: string
{
    case NONE = 'none';
    case AERO = 'aero';
    case LAND = 'land';
    case MARINE = 'marine';

    public function label(): string
    {
        return match ($this) {
            self::AERO => git_get_setting(SettingsKeys::LABEL_TRANSPORT_B, 'Aéreo'),
            self::LAND => git_get_setting(SettingsKeys::LABEL_TRANSPORT_A, 'Terrestre'),
            self::MARINE => git_get_setting(SettingsKeys::LABEL_TRANSPORT_C, 'Marítimo'),
            default => $this->name,
        };
    }

    public function slug(): string
    {
        return match ($this) {
            self::AERO => 'aereo',
            self::LAND => 'land',
            self::MARINE => 'marine',
            default => 'none',
        };
    }

    public static function fromSlug(string $slug)
    {
        return match ($slug) {
            'none' => self::NONE,
            'aereo' => self::AERO,
            'land' => self::LAND,
            'marine' => self::MARINE,
            default => null,
        };
    }
}
