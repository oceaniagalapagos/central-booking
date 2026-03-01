<?php
namespace CentralBooking\QR;

final class ColorQr
{
    private int $r = 0;
    private int $g = 0;
    private int $b = 0;

    private function __construct()
    {
    }

    public static function fromHex(string $hex): ColorQr
    {
        $color = new ColorQr();
        $color->setColorHex($hex);
        return $color;
    }

    public static function fromRGB(int $r, int $g, int $b): ColorQr
    {
        $color = new ColorQr();
        $color->setColorRGB($r, $g, $b);
        return $color;
    }

    public function setColorHex(string $hex)
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 6) {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            $this->setColorRGB($r, $g, $b);
        } elseif (strlen($hex) === 3) {
            $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
            $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
            $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
            $this->setColorRGB($r, $g, $b);
        }
    }

    public function setColorRGB(int $r, int $g, int $b)
    {
        $this->r = $r;
        $this->g = $g;
        $this->b = $b;
    }

    public function getColorRGB(): array
    {
        return [
            'r'=>$this->r,
            'g'=>$this->g,
            'b'=>$this->b
        ];
    }

    public function getColorHex(): string
    {
        return sprintf("#%02x%02x%02x", $this->r, $this->g, $this->b);
    }
}
