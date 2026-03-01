<?php
namespace CentralBooking\GUI;

class FormComponent extends StandaloneComponent
{
    public function __construct()
    {
        parent::__construct('form');
        $this->id = "git_form_" . rand();
    }

    public function getSubmitButton(string $text = 'Submit')
    {
        $button = new TextComponent('button', esc_html($text));
        $button->attributes->set('type', 'submit');
        $button->attributes->set('form', $this->id);
        $button->class_list->add('btn', 'btn-primary');
        return $button;
    }

    public function getResetButton(string $text = 'Reset')
    {
        $button = new TextComponent('button', esc_html($text));
        $button->attributes->set('type', 'reset');
        $button->attributes->set('form', $this->id);
        $button->class_list->add('btn', 'btn-secondary');
        return $button;
    }
}
