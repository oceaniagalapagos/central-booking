<?php
namespace CentralBooking\QR\DefaultStrategy;

use CentralBooking\QR\DataQr;

final class WhatsAppData implements DataQr
{
    public function __construct(
        private readonly string $phoneNumber,
        private readonly ?string $message = null
    ) {
        if (empty($phoneNumber)) {
            throw new \InvalidArgumentException('Phone number cannot be empty');
        } elseif (!preg_match('/^\+?[1-9]\d{1,14}$/', $phoneNumber)) {
            throw new \InvalidArgumentException('Invalid phone number format');
        } elseif (str_contains($phoneNumber, ' ')) {
            throw new \InvalidArgumentException('Phone number cannot contain spaces');
        }
    }

    public function getData(): string
    {
        $url = 'https://wa.me/' . preg_replace('/\D/', '', $this->phoneNumber);
        if ($this->message !== null) {
            $url .= '?text=' . urlencode($this->message);
        }
        return $url;
    }
}