<?php
namespace CentralBooking\GUI;

class Datalist extends BaseComponent
{
    public function __construct(private readonly InputComponent $input, private readonly array $options = [])
    {
        parent::__construct('datalist');
        $this->id = "datalist-{$input->id}-" . rand();
    }

    public function addItem(string $value)
    {
        $this->options[] = $value;
    }

    public function compact()
    {
        $html = parent::compact();
        foreach ($this->options as $option) {
            $html .= "<option value=\"{$option}\">";
        }
        $html .= '</datalist>';
        return $html;
    }
}
