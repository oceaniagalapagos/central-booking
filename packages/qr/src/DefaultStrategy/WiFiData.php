<?php
namespace CentralBooking\QR\DefaultStrategy;

use CentralBooking\QR\DataQr;

final class WiFiData implements DataQr
{
    public function __construct(
        private readonly string $ssid,
        private readonly string $password,
        private readonly string $encryption = 'WPA',
        private readonly bool $hidden = false
    ) {
        if (empty($ssid)) {
            throw new \InvalidArgumentException('SSID cannot be empty');
        }
    }

    public function getData(): string
    {
        $hidden = $this->hidden ? 'true' : 'false';
        return "WIFI:T:{$this->encryption};S:{$this->ssid};P:{$this->password};H:{$hidden};;";
    }
}