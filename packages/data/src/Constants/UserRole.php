<?php
namespace CentralBooking\Data\Constants;

enum UserRole
{
    case CUSTOMER;
    case OPERATOR;
    case MARKETER;
    case ADMINISTRATOR;

    public function label()
    {
        return match ($this) {
            self::CUSTOMER => 'Cliente',
            self::OPERATOR => 'Operador',
            self::MARKETER => 'Comercializador',
            self::ADMINISTRATOR => 'Administrador',
            default => $this->name,
        };
    }

    public function slug()
    {
        return match ($this) {
            self::CUSTOMER => 'customer',
            self::OPERATOR => 'operator',
            self::MARKETER => 'marketer',
            self::ADMINISTRATOR => 'administrator',
            default => $this->name,
        };
    }
}
