<?php
namespace CentralBooking\GUI\Constants;

class ListConstants
{
    public const ORDER = 'order';
    public const UNORDER = 'unorder';

    public static function is_valid(string $type)
    {
        $allowed = [self::ORDER, self::UNORDER];
        return in_array($type, $allowed);
    }
}