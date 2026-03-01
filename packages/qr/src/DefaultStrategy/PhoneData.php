<?php
namespace CentralBooking\QR\DefaultStrategy;

use CentralBooking\QR\DataQr;

final class PhoneData implements DataQr
{
    public function __construct(
        private readonly string $phoneNumber
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
        return 'tel:' . $this->phoneNumber;
    }
}