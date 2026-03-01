<?php
namespace CentralBooking\GUI;

class CompositeComponent extends BaseComponent
{
    /**
     * @var array<ComponentInterface> $childs
     */
    protected array $childs = [];

    public function __construct(string $tag = 'div')
    {
        parent::__construct($tag);
    }

    public function addChild(ComponentInterface $component)
    {
        $this->childs[] = $component;
        return $this;
    }

    public function compact()
    {
        $html = parent::compact();
        foreach ($this->childs as $child) {
            $html .= $child->compact();
        }
        $html .= "</{$this->tag}>";
        return $html;
    }
}
