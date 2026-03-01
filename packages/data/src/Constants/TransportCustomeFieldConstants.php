<?php
namespace CentralBooking\Data\Constants;

enum TransportCustomeFieldConstants: string
{
    case TEXT = 'text';
    case ACTION = 'action';
    case IA_PROMPT = 'ia_prompt';
    case BRAND_BANNER = 'brand_banner';

    public function label()
    {
        return match ($this) {
            self::TEXT => 'Texto',
            self::ACTION => 'Acción',
            self::IA_PROMPT => 'Prompt de IA',
            self::BRAND_BANNER => 'Banner de transporte',
            default => $this->name,
        };
    }

    public function slug()
    {
        return match ($this) {
            self::TEXT => 'text',
            self::ACTION => 'action',
            self::IA_PROMPT => 'ia_prompt',
            self::BRAND_BANNER => 'brand_banner',
            default => $this->name,
        };
    }
}