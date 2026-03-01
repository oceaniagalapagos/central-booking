<?php
namespace CentralBooking\GUI;

class InputComponent extends FormControlComponent
{
    private ?Datalist $datalist = null;

    public function __construct(string $name, string $type = 'text')
    {
        parent::__construct($name);
        $this->id = "input-$name-" . rand();
        $this->attributes->set('type', $type);
    }

    public function compact()
    {
        $html = parent::compact();
        if ($this->datalist !== null) {
            $html .= $this->datalist->compact();
        }
        return $html;
    }

    public function setValue($value)
    {
        $this->attributes->set('value', $value);
    }

    public function setPlaceholder(string $placeholder)
    {
        $this->attributes->set('placeholder', $placeholder);
    }

    public function setDatalist($values = [])
    {
        $this->datalist = new Datalist($this, $values);
        $this->attributes->set('list', $this->datalist->id);
    }
}
