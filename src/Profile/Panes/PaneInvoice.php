<?php
namespace CentralBooking\Profile\Panes;

use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\Profile\Forms\FormInvoice;
use CentralBooking\Profile\Tables\TableInvoice;

final class PaneInvoice implements DisplayerInterface
{
    public function render()
    {
        (new FormInvoice)->render();
        (new TableInvoice)->render();
    }
}
