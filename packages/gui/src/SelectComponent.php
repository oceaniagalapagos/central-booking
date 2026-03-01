<?php
namespace CentralBooking\GUI;

class SelectComponent extends FormControlComponent
{
    /**
     * @var array<TextComponent>
     */
    protected array $options = [];

    public function __construct(
        protected string $name,
        protected string $title = 'Select an option'
    ) {
        parent::__construct($name);
        $this->tag = 'select';
        $this->attributes->set('title', $this->title);
    }

    public function addOption(
        string $key,
        string|int $value = '',
        bool $selected = false,
        array $attributes = [],
    ) {
        $option = new TextComponent('option', $key);
        if ($value !== null) {
            $option->attributes->set('value', git_serialize($value));
        }
        if ($selected) {
            $option->attributes->set('selected', '');
        }
        if (isset($attributes['id'])) {
            $option->id = $attributes['id'];
            unset($attributes['id']);
        }
        if (isset($attributes['class'])) {
            if (is_array($attributes['class'])) {
                foreach ($attributes['class'] as $class) {
                    $option->class_list->add($class);
                }
            } else {
                $option->class_list->add($attributes['class']);
            }
            unset($attributes['class']);
        }
        foreach ($attributes as $name => $content) {
            $option->attributes->set($name, $content);
        }
        $this->options[] = $option;
    }

    public function removeOption(string|int $value = '')
    {
        foreach ($this->options as $index => $option) {
            if ($option->getText() == $value) {
                unset($this->options[$index]);
                break;
            }
        }
    }

    public function compact()
    {
        $html = parent::compact();
        foreach ($this->options as $option) {
            $html .= $option->compact();
        }
        $html .= "</{$this->tag}>";
        return $html;
    }

    public function setValue(mixed $value)
    {
        foreach ($this->options as $option) {
            if (
                $option->attributes->get('value') !== null &&
                $option->attributes->get('value') == $value
            ) {
                $option->attributes->set('selected', '');
                break;
            }
        }
    }
}
