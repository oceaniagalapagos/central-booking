<?php
namespace CentralBooking\GUI;

class CounterComponent extends BaseComponent
{
    private int $min;
    private int $max;

    public function __construct(?int $min = null, ?int $max = null)
    {
        parent::__construct('div');
        $this->class_list->add('git-counter');
        $this->id = 'git-counter-' . uniqid();
        if ($min !== null) {
            $this->attributes->set('data-min-value', $min);
        }
        if ($max !== null) {
            $this->attributes->set('data-max-value', $max);
        }
        wp_enqueue_style(
            'central-tickets-counter-component',
            CENTRAL_BOOKING_URL . 'packages/gui/assets/css/counter-component.css',
            [],
            '1.0.0'
        );
        wp_enqueue_script(
            'central-tickets-counter-component',
            CENTRAL_BOOKING_URL . 'packages/gui/assets/js/counter-component.js',
            ['jquery'],
            '1.0.0',
            true
        );
    }

    public function compact(): string
    {
        $html = parent::compact();
        $html .= '<span class="counter-control counter-decrement">-</span>';
        $html .= '<span class="counter-control counter-value">0</span>';
        $html .= '<span class="counter-control counter-increment">+</span>';
        $html .= "</{$this->tag}>";
        return $html;
    }

    public function set_min(int $min): void
    {
        $this->min = $min;
    }

    public function set_max(int $max): void
    {
        $this->max = $max;
    }
}
