<?php
namespace CentralBooking\Implementation\Temp;

enum MessageLevel
{
    case INFO;
    case WARNING;
    case ERROR;
    case SUCCESS;

    public static function fromString(string $level): MessageLevel
    {
        return match (strtoupper($level)) {
            'INFO' => self::INFO,
            'WARNING' => self::WARNING,
            'ERROR' => self::ERROR,
            'SUCCESS' => self::SUCCESS,
            default => self::INFO,
        };
    }
}
