<?php
namespace CentralBooking\Webhook;

enum WebhookStatus: string
{
    case ACTIVE = 'active';
    case IN_PAUSE = 'in_pause';
    case DISABLED = 'disabled';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Activo',
            self::IN_PAUSE => 'En pausa',
            self::DISABLED => 'Deshabilitado',
            default => 'Estado desconocido',
        };
    }

    public function slug(): string
    {
        return match ($this) {
            self::ACTIVE => 'active',
            self::IN_PAUSE => 'in_pause',
            self::DISABLED => 'disabled',
            default => $this->name,
        };
    }

    public static function fromSlug(string $slug)
    {
        return match ($slug) {
            'active' => self::ACTIVE,
            'in_pause' => self::IN_PAUSE,
            'disabled' => self::DISABLED,
            default => null,
        };
    }
}