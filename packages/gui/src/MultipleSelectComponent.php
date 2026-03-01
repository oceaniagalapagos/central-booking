<?php
namespace CentralBooking\GUI;

class MultipleSelectComponent extends SelectComponent
{
    private array $values = [];

    public function __construct(string $name = '')
    {
        parent::__construct($name);
        $this->attributes->remove('name');
        $this->attributes->set('data-name', $name);
        $this->class_list->add('git-multiselect');
        wp_enqueue_style(
            'git-multiselect_style',
            CENTRAL_BOOKING_URL . 'packages/gui/assets/css/multiselect-component.css'
        );
        wp_enqueue_script_module(
            'git-multiselect_script',
            CENTRAL_BOOKING_URL . 'packages/gui/assets/js/multiselect-component.js'
        );
    }

    public function compact()
    {
        $this->attributes->set('data-selected', git_serialize($this->values));
        return parent::compact();
    }

    public function setValue($value)
    {
        $result = in_array($value, $this->values);
        if (!$result) {
            $this->values[] = $value;
        }
    }

    /**
     * @return BaseComponent
     */
    public function getOptionsContainer()
    {
        $options_container = new CompositeComponent('div');
        $options_container->id = "{$this->id}-container";
        $options_container->class_list->add('git-multiselect-container');

        foreach ($this->values as $value) {
            // Buscar el texto de la opción seleccionada
            $option_text = '';
            foreach ($this->options as $option) {
                if ($option->attributes->get('value') == $value) {
                    $option_text = $option->getText();
                    break;
                }
            }

            $option_selected = new CompositeComponent('div');
            $option_text = new TextComponent('span', $option_text);
            $input_value = new StandaloneComponent('input');
            $option_text->class_list->add('option-text');
            $option_selected->class_list->add('git-option-item-selected');
            $option_selected->attributes->set('data-value', $value);
            $input_value->attributes->set('type', 'hidden');
            $input_value->attributes->set('name', $this->name . '[]');
            $input_value->attributes->set('value', $value);

            $remove_btn = new TextComponent('i');
            $remove_btn->class_list->add(
                'bi',
                'bi-x',
                'git-remove-option'
            );

            $option_selected->addChild($option_text);
            $option_selected->addChild($remove_btn);
            $option_selected->addChild($input_value);

            $options_container->addChild($option_selected);
        }

        return $options_container;
    }
}
