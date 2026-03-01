<?php
namespace CentralBooking\QR\DefaultStrategy;

use CentralBooking\QR\DataQr;
final class EmailData implements DataQr
{
    public function __construct(
        private readonly string $emailAddress,
        private readonly ?string $subject = null,
        private readonly ?string $body = null
    ) {
        if (empty($emailAddress)) {
            throw new \InvalidArgumentException('Email address cannot be empty');
        } elseif (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email address format');
        }
    }

    public function getData(): string
    {
        $mailTo = 'mailto:' . $this->emailAddress;
        $params = [];
        if ($this->subject !== null) {
            $params[] = 'subject=' . urlencode($this->subject);
        }
        if ($this->body !== null) {
            $params[] = 'body=' . urlencode($this->body);
        }
        if (!empty($params)) {
            $mailTo .= '?' . implode('&', $params);
        }
        return $mailTo;
    }
}