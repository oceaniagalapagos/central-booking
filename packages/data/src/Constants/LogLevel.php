<?php
namespace CentralBooking\Data\Constants;

enum LogLevel
{
    case INFO;
    case ERROR;
    case WARNING;

    public function label(): string
    {
        return match ($this) {
            self::INFO => 'info',
            self::ERROR => 'error',
            self::WARNING => 'warning',
        };
    }
}
