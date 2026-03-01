<?php
namespace CentralBooking\Data\Constants;

use CentralBooking\Admin\Setting\SettingsKeys;

enum TicketStatus
{
    case NONE;
    case CANCEL;
    case PAYMENT;
    case PENDING;
    case PARTIAL;
    case PERORDER;

    public function label()
    {
        return match ($this) {
            self::CANCEL => git_get_setting(SettingsKeys::LABEL_TICKET_CANCEL, 'Anulado'),
            self::PAYMENT => git_get_setting(SettingsKeys::LABEL_TICKET_PAYMENT, 'Pagado'),
            self::PARTIAL => git_get_setting(SettingsKeys::LABEL_TICKET_PARTIAL, 'Parcial'),
            self::PENDING => git_get_setting(SettingsKeys::LABEL_TICKET_PENDING, 'Pendiente'),
            self::PERORDER => git_get_setting(SettingsKeys::LABEL_TICKET_PREORDER, 'Preorden'),
            default => $this->name
        };
    }

    public function slug()
    {
        return match ($this) {
            self::CANCEL => 'cancel',
            self::PAYMENT => 'payment',
            self::PARTIAL => 'partial',
            self::PENDING => 'pending',
            self::PERORDER => 'preorder',
            default => $this->name
        };
    }

    public static function fromSlug(string $slug)
    {
        return match ($slug) {
            'cancel' => self::CANCEL,
            'payment' => self::PAYMENT,
            'partial' => self::PARTIAL,
            'pending' => self::PENDING,
            'preorder' => self::PERORDER,
            default => null
        };
    }
}
