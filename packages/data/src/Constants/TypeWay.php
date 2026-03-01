<?php
namespace CentralBooking\Data\Constants;

use CentralBooking\Admin\Setting\SettingsKeys;

enum TypeWay: string
{
    case NONE = 'none';
    case ONE_WAY = 'one_way';
    case DOUBLE_WAY = 'double_way';
    case ANY_WAY = 'any_way';

    public function label(): string
    {
        return match ($this) {
            self::NONE => git_get_setting(SettingsKeys::LABEL_ROUTE_NONE, 'Ninguno'),
            self::ONE_WAY => git_get_setting(SettingsKeys::LABEL_ROUTE_ONE_WAY, 'Ida'),
            self::ANY_WAY => git_get_setting(SettingsKeys::LABEL_ROUTE_ANY_WAY, 'Ambos'),
            self::DOUBLE_WAY => git_get_setting(SettingsKeys::LABEL_ROUTE_DOUBLE_WAY, 'Ida y Vuelta'),
            default => $this->name,
        };
    }

    public function slug(): string
    {
        return match ($this) {
            self::NONE => 'none',
            self::ONE_WAY => 'one_way',
            self::ANY_WAY => 'any_way',
            self::DOUBLE_WAY => 'double_way',
            default => $this->name,
        };
    }

    public static function fromSlug(string $slug)
    {
        return match ($slug) {
            'none' => self::NONE,
            'one_way' => self::ONE_WAY,
            'any_way' => self::ANY_WAY,
            'double_way' => self::DOUBLE_WAY,
            default => null,
        };
    }
}
