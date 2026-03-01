<?php
namespace CentralBooking\Data\Services;

use CentralBooking\Data\Constants\LogLevel;
use CentralBooking\Data\Constants\LogSource;

final class LogItem
{
    public function __construct(
        public int $id,
        public LogSource $source,
        public LogLevel $level,
        public int $id_source,
        public string $message,
    ) {
    }
}
