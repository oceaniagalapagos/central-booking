<?php
namespace CentralBooking\GUI;

use CentralBooking\GUI\ComponentInterface;

class AccordionComponent extends BaseComponent
{
    private array $items = [];

    public function __construct(private bool $multiple_open = false)
    {
        parent::__construct('div');
        $this->id = 'accordion-' . rand();
        $this->class_list->add('git-accordion');
        wp_enqueue_script_module(
            'accordion-component-script',
            CENTRAL_BOOKING_URL . 'packages/gui/assets/js/accordion-component.js',
        );
        wp_enqueue_style(
            'accordion-component-style',
            CENTRAL_BOOKING_URL . 'packages/gui/assets/css/accordion-component.css'
        );
    }

    public function addItem($label, ComponentInterface $content, bool $is_open = false)
    {
        $this->items[] = [
            'label' => $label,
            'is_open' => $is_open,
            'content' => $content,
        ];
    }

    public function compact()
    {
        $html = parent::compact();
        foreach ($this->items as $item) {
            $html .= $this->create_item(
                $item['label'],
                $item['content'],
                $item['is_open'],
            )->compact();
        }
        $html .= '</div>';
        return $html;
    }

    private function create_item($header, ComponentInterface $content, bool $is_open)
    {
        $id_item = 'accordion-item-' . rand();
        $container = new CompositeComponent('div');
        $container->class_list->add('accordion-item');
        return $container
            ->addChild($this->create_item_header(
                $header,
                $id_item
            ))
            ->addChild($this->create_item_body(
                $content,
                $id_item,
                $is_open,
            ));
    }

    private function create_item_header($text, string $id_item)
    {
        $header = new CompositeComponent('div');
        $header->class_list->add('accordion-header');
        $button = new ButtonComponent($text);
        $button->class_list->add('accordion-button');
        $button->attributes->set('data-target', "#$id_item");
        $header->addChild($button);
        return $header;
    }

    private function create_item_body(ComponentInterface $content, string $id_item, bool $is_open)
    {
        $meta = [
            'id' => $id_item,
            'class' => 'accordion-collapse collapse' . ($is_open ? ' show' : ''),
        ];
        if (!$this->multiple_open) {
            $meta = array_merge(
                $meta,
                ['data-parent' => "#$this->id",]
            );
        }
        $container = new CompositeComponent('div');
        foreach ($meta as $key => $value) {
            $container->attributes->set($key, $value);
        }
        $body = new CompositeComponent('div');
        $body->class_list->add('accordion-body');
        $body->addChild($content);
        $container->addChild($body);
        return $container;
    }
}
