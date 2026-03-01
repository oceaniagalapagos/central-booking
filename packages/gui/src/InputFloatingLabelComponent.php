<?php
namespace CentralBooking\GUI;

class InputFloatingLabelComponent extends BaseComponent
{
    public function __construct(
        private readonly FormControlComponent $input,
        private readonly string $text
    ) {
        parent::__construct('div');
        $this->class_list->add('form-floating');
    }

    public function compact(): string
    {
        $html = parent::compact();
        $html .= $this->input->compact();
        $html .= $this->input->getLabel($this->text)->compact();
        $html .= "</{$this->tag}>";
        return $html;
    }
}
