<?php
namespace CentralBooking\GUI;
/**
 * A specialized component for managing and rendering text within a base component.
 */
class TextComponent extends BaseComponent
{
    private string $text = '';

    /**
     * Constructs a new TextComponent instance with an optional tag, text, and metadata.
     *
     * @param string $tag The HTML tag to use for this component. Defaults to 'p'.
     * @param string $text The initial text content of the component. Defaults to an empty string.
     * @param array $meta An associative array of attributes for the component. Defaults to an empty array.
     */
    public function __construct(string $tag = 'p', string $text = '')
    {
        parent::__construct($tag);
        $this->append($text);
    }

    public function getText()
    {
        return $this->text;
    }

    /**
     * Appends additional text content to this component.
     *
     * @param string|ComponentInterface $text The text content to append.
     * @return self Returns the current instance for method chaining.
     */
    public function append($text): self
    {
        $this->text .= $text instanceof ComponentInterface ? $text->compact() : htmlspecialchars($text);
        return $this;
    }

    /**
     * Generates the HTML-like representation of the text component.
     *
     * This method delegates the rendering to the underlying BaseComponent instance.
     *
     * @return string The rendered HTML-like string of the text component.
     */
    public function compact()
    {
        $html = parent::compact();
        $html .= $this->text;
        $html .= "</{$this->tag}>";
        return $html;
    }

    public static function create(string $tag, string $text, array $attr = [])
    {
        $instance = new self($tag, $text);
        foreach ($attr as $key => $value) {
            $key_trim = trim($key);
            $value_trim = trim($value);
            if ($key_trim === 'id') {
                $instance->id = $value_trim;
                continue;
            }
            if ($key_trim === 'class') {
                $instance->class_list->add(...explode(' ', $value_trim));
                continue;
            }
            $instance->attributes->set($key_trim, $value_trim);
        }
        return $instance;
    }
}
