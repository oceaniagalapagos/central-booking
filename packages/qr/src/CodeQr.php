<?php
namespace CentralBooking\QR;

final class CodeQr
{
    private function __construct(
        private readonly DataQr $data,
        private readonly ErrorCorrectionCode $errorCorrectionCode,
        private readonly int $size,
        private readonly int $margin,
        private readonly ?ColorQr $color,
        private readonly ?ColorQr $bgColor
    ) {
    }

    public static function create(
        DataQr $data,
        ErrorCorrectionCode $errorCorrectionCode = ErrorCorrectionCode::LOW,
        int $size = 200,
        int $margin = 10,
        ?ColorQr $color = null,
        ?ColorQr $bgColor = null
    ) {
        $color = $color ?? ColorQr::fromHex('#000000');
        $bgColor = $bgColor ?? ColorQr::fromHex('#ffffff');

        if ($margin > 50 || $margin < 0) {
            throw new \InvalidArgumentException('Margin cannot be greater than 50 or less than 0');
        } elseif ($size < 10) {
            throw new \InvalidArgumentException('Size cannot be less than 10');
        } else {
            $instance = new self(
                $data,
                $errorCorrectionCode,
                $size,
                $margin,
                $color,
                $bgColor
            );
            return $instance;
        }
    }

    public function compact(string $title = 'QR Code')
    {
        $url = $this->getUrlCode();
        return '<img src="' . esc_url($url) . '" alt="' . esc_attr($title) . '">';
    }

    public function getUrlCode(): string
    {
        $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/';
        $apiUrl = add_query_arg([
            'data' => $this->data->getData(),
            'size' => $this->size . 'x' . $this->size,
            'ecc' => $this->errorCorrectionCode->slug(),
            'margin' => $this->margin,
            'color' => join('-', array_values($this->color->getColorRGB() ?? [0, 0, 0])),
            'bgcolor' => join('-', array_values($this->bgColor->getColorRGB() ?? [255, 255, 255])),
            'format' => 'png',
        ], $apiUrl);
        return $apiUrl;
    }
}
