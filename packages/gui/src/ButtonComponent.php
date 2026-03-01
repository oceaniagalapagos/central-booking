<?php
namespace CentralBooking\GUI;

use CentralBooking\GUI\Constants\ButtonActionConstants;
use CentralBooking\GUI\Constants\ButtonStyleConstants;

class ButtonComponent extends BaseComponent
{
    private string $text;
    private string $type = ButtonActionConstants::BUTTON;
    private string $style = ButtonStyleConstants::NONE;

    /**
     * @param string|ComponentInterface $text
     * @param string $type
     * @param string $style
     */
    public function __construct(
        $text,
        string $type = ButtonActionConstants::BUTTON,
        string $style = ButtonStyleConstants::NONE,
    ) {
        parent::__construct('button');
        $this->set_text($text);
        $this->set_type($type);
        $this->set_style($style);
    }

    public function compact(): string
    {
        $this->attributes->set('type', $this->type);
        $this->class_list->add(...explode(' ', $this->style));
        $html = parent::compact();
        $html .= $this->text;
        $html .= "</{$this->tag}>";
        return $html;
    }

    public function set_text(string|ComponentInterface $text)
    {
        $this->text = $text instanceof ComponentInterface ? $text->compact() : htmlspecialchars($text);
    }

    public function set_type(string $type)
    {
        $this->type = htmlspecialchars($type);
    }

    public function set_style(string $style)
    {
        $this->style = htmlspecialchars($style);
    }
}
