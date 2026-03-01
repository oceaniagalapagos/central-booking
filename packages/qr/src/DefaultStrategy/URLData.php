<?php
namespace CentralBooking\QR\DefaultStrategy;

use CentralBooking\QR\DataQr;

final class URLData implements DataQr
{
    public function __construct(
        private readonly string $url
    ) {
        if (empty($url)) {
            throw new \InvalidArgumentException('URL cannot be empty');
        } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid URL format');
        }
    }

    public function getData(): string
    {
        return urlencode($this->url);
    }
}
