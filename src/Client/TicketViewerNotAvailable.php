<?php
namespace CentralBooking\Client;

use CentralBooking\GUI\ComponentInterface;
use CentralBooking\GUI\CompositeComponent;

class TicketViewerNotAvailable implements ComponentInterface
{
    public function compact()
    {
        $container = new CompositeComponent();
        return $container->compact();
    }
}
