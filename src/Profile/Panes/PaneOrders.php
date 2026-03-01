<?php
namespace CentralBooking\Profile\Panes;

use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\Profile\Forms\FormTickets;
use CentralBooking\Profile\Tables\TableOrder;
use CentralBooking\Profile\Tables\TableOrderTickets;

final class PaneOrders implements DisplayerInterface
{
    public function render()
    {
        if (!isset($_GET['action'])) {
            (new TableOrder())->render();
        } elseif ($_GET['action'] === 'view_order') {
            (new TableOrderTickets())->render();
        } elseif ($_GET['action'] === 'edit_flexible') {
            (new FormTickets())->render();
        }
    }
}