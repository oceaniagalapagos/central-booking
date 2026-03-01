<?php
namespace CentralBooking\Profile\Panes;

use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\Profile\Forms\FormTrip;
use CentralBooking\Profile\Tables\TableTrip;

final class PaneTrip implements DisplayerInterface
{

    public function render()
    {
        (new FormTrip())->render();
        (new TableTrip())->render();
    }
}
