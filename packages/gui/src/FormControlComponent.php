<?php
namespace CentralBooking\GUI;

abstract class FormControlComponent extends StandaloneComponent
{
    public function __construct(string $name = '')
    {
        parent::__construct('input');
        $this->id = "input-$name-" . rand();
        $this->class_list->add('form-control');
        $this->attributes->set('name', $name);
    }

    public function getLabel(string $text)
    {
        $label = new TextComponent('label', $text);
        if ($this->attributes->get('required') !== null) {
            $label->append(ComponentBuilder::create('<span class="required">*</span>'));
        }
        $label->class_list->add('form-label');
        if (!empty($this->id)) {
            $label->attributes->set('for', $this->id);
        }
        return $label;
    }

    public function setDisabled(bool $disabled)
    {
        if ($disabled) {
            $this->attributes->set('disabled', '');
        } else {
            $this->attributes->remove('disabled');
        }
    }

    public function setRequired(bool $required)
    {
        if ($required) {
            $this->attributes->set('required', '');
        } else {
            $this->attributes->remove('required');
        }
    }

    /**
     * @param bool|int|string $value
     * @return void
     */
    abstract public function setValue(mixed $value);
}
