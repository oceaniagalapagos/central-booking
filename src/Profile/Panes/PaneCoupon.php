<?php
namespace CentralBooking\Profile\Panes;

use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\Profile\Forms\FormEditCoupon;
use CentralBooking\Profile\Forms\FormSearchCoupon;
use CentralBooking\Profile\Tables\TableCoupon;

final class PaneCoupon implements DisplayerInterface
{
    public function render()
    {
        $action = $_GET['action'] ?? 'search';
        if ($action === 'edit') {
            (new FormEditCoupon())->render();
        } elseif ($action === 'search') {
            (new FormSearchCoupon())->render();
            (new TableCoupon())->render();
        } else {
            (new PaneDefault)->render();
        }
    }
}
