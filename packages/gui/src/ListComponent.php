<?php
namespace CentralBooking\GUI;

use CentralBooking\GUI\Constants\ListConstants;

class ListComponent extends BaseComponent
{
    /**
     * @var array<string>
     */
    private array $items = [];

    public function __construct(string $type = ListConstants::UNORDER)
    {
        $tag = $this->get_type($type);
        parent::__construct($tag);
    }

    /**
     * @param ListComponent|ComponentInterface|string $item
     * @param array $attr
     * @return static
     */
    public function add_item($item, array $attr = []): self
    {
        $attr_str = '';
        if ($item instanceof ListComponent) {
            foreach ($attr as $key => $value) {
                $item->attributes->set($key, $value);
            }
            $this->items[] = $item->compact();
        } else if ($item instanceof Component) {
            foreach ($attr as $key => $value) {
                $attr_str .= " {$key}=\"{$value}\" ";
            }
            $this->items[] = "<li {$attr_str}>" . $item->compact() . "</li>";
        } else {
            foreach ($attr as $key => $value) {
                $attr_str .= " {$key}=\"{$value}\" ";
            }
            $this->items[] = "<li {$attr_str}>" . htmlspecialchars($item) . "</li>";
        }
        return $this;
    }

    private function get_type(string $type)
    {
        $tag = 'ul';
        if ($type === ListConstants::ORDER) {
            $tag = 'ol';
        } elseif ($type === ListConstants::UNORDER) {
            $tag = 'ul';
        }
        return $tag;
    }

    public function compact()
    {
        $html = parent::compact();
        foreach ($this->items as $item) {
            $html .= $item;
        }
        $html .= "</{$this->tag}>";
        return $html;
    }
}
