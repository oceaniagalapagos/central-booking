<?php
namespace CentralBooking\GUI;

abstract class BaseComponent implements ComponentInterface, DisplayerInterface
{
    public string $id = '';
    public TokenMap $styles;
    public TokenList $class_list;
    public TokenMap $attributes;
    protected string $tag = '';

    /**
     * BaseComponent constructor.
     *
     * @param string $tag The HTML tag for the component.
     */
    public function __construct(string $tag)
    {
        $this->tag = $tag;
        $this->styles = new TokenMap;
        $this->attributes = new TokenMap;
        $this->class_list = new TokenList;
    }

    public function compact()
    {
        $meta = '';

        if ($this->id !== '') {
            $meta .= " id=\"" . htmlspecialchars($this->id) . "\"";
        }

        $classList = implode(' ', $this->class_list->values());
        if ($classList !== '') {
            $meta .= " class=\"" . htmlspecialchars($classList) . "\"";
        }

        $styleList = implode('; ', array_map(
            fn($key) => "$key: " . htmlspecialchars($this->styles->get($key)),
            $this->styles->keys()
        ));

        if ($styleList !== '') {
            $meta .= " style=\"" . htmlspecialchars($styleList) . "\"";
        }

        $keys = $this->attributes->keys();
        foreach ($keys as $key) {
            $meta .= " $key=\"" . htmlspecialchars($this->attributes->get($key)) . "\"";
        }

        $output = "<{$this->tag}$meta>";

        return $output;
    }

    public function render()
    {
        echo $this->compact();
    }
}