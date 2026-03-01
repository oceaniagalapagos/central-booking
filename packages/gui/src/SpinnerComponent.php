<?php
namespace CentralBooking\GUI;
class SpinnerComponent implements ComponentInterface
{
    private CompositeComponent $container;

    public function __construct(string $type = SpinnerConstants::PRIMARY)
    {
        $this->container = new CompositeComponent;
        $content = new TextComponent('span', 'Loading...');

        $this->container->class_list->add('spinner-border');
        $this->container->class_list->add("text-{$type}");
        $this->container->attributes->set('role', 'status');

        $content->class_list->add('visually-hidden');
        $this->container->addChild($content);
    }

    public function compact()
    {
        return $this->container->compact();
    }
}
