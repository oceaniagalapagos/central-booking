<?php
namespace CentralBooking\GUI;

class TextareaComponent extends InputComponent
{
    protected string $inner_text = '';

    public function __construct(string $name = '')
    {
        parent::__construct($name);
        $this->tag = 'textarea';
        $this->id = "textarea-$name-" . rand();
        $this->attributes->remove('type');
    }

    public function setValue(mixed $value)
    {
        $this->inner_text = (string) $value;
    }

    public function compact()
    {
        $html = parent::compact();
        $html .= $this->inner_text;
        $html .= "</{$this->tag}>";
        return $html;
    }
}
