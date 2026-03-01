<?php

use CentralBooking\QR\CodeQr;
use CentralBooking\QR\ColorQr;
use CentralBooking\QR\DataQr;
use CentralBooking\QR\DefaultStrategy\EmailData;
use CentralBooking\QR\DefaultStrategy\PhoneData;
use CentralBooking\QR\DefaultStrategy\URLData;
use CentralBooking\QR\DefaultStrategy\WhatsAppData;
use CentralBooking\QR\DefaultStrategy\WiFiData;
use CentralBooking\QR\ErrorCorrectionCode;

/**
 * @param DataQr $data
 * @param array{size:int,margin:int,color:ColorQr,color_hex:string,color_rgb:array{r:int,g:int,b:int},color_bg:ColorQr,color_bg_hex:string,color_bg_rgb:array{r:int,g:int,b:int},ecc:string|ErrorCorrectionCode} $params
 * @return CodeQr
 */
function git_qr_code(DataQr $data, array $params = [])
{
    $size = 350;
    $margin = 10;
    $color = ColorQr::fromHex('#000000');
    $bgColor = ColorQr::fromHex('#ffffff');
    $errorCorrectionCode = ErrorCorrectionCode::LOW;

    if (isset($params['size'])) {
        $size = (int) $params['size'];
    }

    if (isset($params['margin'])) {
        $margin = (int) $params['margin'];
    }

    if (isset($params['color'])) {
        $color = $params['color'];
    } else if (isset($params['color_hex']) && preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $params['color_hex'])) {
        $color = ColorQr::fromHex($params['color_hex']);
    } else if (isset($params['color_rgb'])) {
        $colors = $params['color_rgb'];
        $color = ColorQr::fromRGB(
            (int) $colors['r'],
            (int) $colors['g'],
            (int) $colors['b'],
        );
    }

    if (isset($params['bg_color'])) {
        $bgColor = $params['bg_color'];
    } else if (isset($params['bg_color_hex']) && preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $params['bg_color_hex'])) {
        $bgColor = ColorQr::fromHex($params['bg_color_hex']);
    } else if (isset($params['bg_color_rgb'])) {
        $colors = $params['bg_color_rgb'];
        $bgColor = ColorQr::fromRGB(
            (int) $colors['r'],
            (int) $colors['g'],
            (int) $colors['b'],
        );
    }

    if (isset($params['ecc'])) {
        if (is_string($params['ecc'])) {
            $errorCorrectionCode = ErrorCorrectionCode::fromSlug($params['ecc']) ?? ErrorCorrectionCode::LOW;
        } else {
            $errorCorrectionCode = $params['ecc'];
        }
    }

    $code_qr = CodeQr::create(
        $data,
        $errorCorrectionCode,
        $size,
        $margin,
        $color,
        $bgColor
    );

    return $code_qr;
}

function get_qr_data_wifi(string $ssid, string $password, $encryption = 'WPA', $hidden = false)
{
    $encryptionAllowed = ['WPA', 'WEP', 'nopass'];
    if (in_array($encryption, $encryptionAllowed) === false) {
        $encryption = 'WPA';
    }
    return new WiFiData($ssid, $password, $encryption, $hidden);
}

function git_qr_data_phone(string $phone): DataQr
{
    return new PhoneData($phone);
}

function git_qr_data_email(string $email_address, ?string $subject = null, ?string $body = null): DataQr
{
    return new EmailData($email_address, $subject, $body);
}

function git_qr_data_url(string $url): DataQr
{
    return new URLData(trim($url));
}

function git_qr_data_whatsapp(string $phone_number, ?string $message = null): DataQr
{
    return new WhatsAppData($phone_number, $message);
}

function git_qr_data(string $data): DataQr
{
    return new class ($data) implements DataQr {
        private function __construct(private readonly string $data)
        {
        }

        public function getData(): string
        {
            return $this->data;
        }
    };
}

function git_qr_color_from_hex(string $hex)
{
    return ColorQr::fromHex($hex);
}

function git_qr_color_from_rgb(int $r, int $g, int $b)
{
    $params = [$r, $g, $b];
    foreach ($params as $value) {
        if ($value < 0 || $value > 255) {
            return null;
        }
    }
    return ColorQr::fromRGB($r, $g, $b);
}