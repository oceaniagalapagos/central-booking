<?php
namespace CentralBooking\Webhook;

enum WebhookTopic
{
    case NONE;
    case COUPON_USED;
    case TICKET_CREATE;
    case TICKET_UPDATE;
    case INVOICE_UPLOAD;
    case PASSENGER_SERVED;
    case PASSENGER_APPROVED;
    case PASSENGER_TRANSFERRED;

    public function label()
    {
        return match ($this) {
            self::COUPON_USED => 'Cupón utilizado',
            self::TICKET_CREATE => 'Ticket creado',
            self::INVOICE_UPLOAD => 'Factura subida',
            self::TICKET_UPDATE => 'Ticket actualizado',
            self::PASSENGER_SERVED => 'Pasajero atendido',
            self::PASSENGER_APPROVED => 'Pasajero aprobado',
            self::PASSENGER_TRANSFERRED => 'Pasajero trasladado',
            default => 'Estado desconocido',
        };
    }

    public function slug()
    {
        return match ($this) {
            self::COUPON_USED => 'coupon_used',
            self::TICKET_CREATE => 'ticket_create',
            self::INVOICE_UPLOAD => 'invoice_upload',
            self::TICKET_UPDATE => 'ticket_update',
            self::PASSENGER_SERVED => 'passenger_served',
            self::PASSENGER_APPROVED => 'passenger_approved',
            self::PASSENGER_TRANSFERRED => 'passenger_transferred',
            default => 'Estado desconocido',
        };
    }

    public static function fromSlug(string $slug)
    {
        return match ($slug) {
            'none' => self::NONE,
            'coupon_used' => self::COUPON_USED,
            'ticket_create' => self::TICKET_CREATE,
            'invoice_upload' => self::INVOICE_UPLOAD,
            'ticket_update' => self::TICKET_UPDATE,
            'passenger_served' => self::PASSENGER_SERVED,
            'passenger_approved' => self::PASSENGER_APPROVED,
            'passenger_transferred' => self::PASSENGER_TRANSFERRED,
            default => null,
        };
    }
}