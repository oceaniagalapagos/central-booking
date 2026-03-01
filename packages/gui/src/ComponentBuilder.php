<?php
namespace CentralBooking\GUI;

final class ComponentBuilder
{
    public static function create(string $component): ComponentInterface
    {
        return new class ($component) implements ComponentInterface {
            public function __construct(private string $component)
            {
            }
            public function compact()
            {
                return $this->component;
            }
        };
    }
}
