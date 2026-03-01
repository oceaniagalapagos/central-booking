<?php
namespace CentralBooking\WooCommerce;

use CentralBooking\Data\Services\TicketService;
use WC_Order;

class CreateTicket
{
    private TicketService $service;
    
    public function __construct() {
        $this->service = new TicketService();
    }

    public function create(WC_Order $order)
    {
        $saved = $order->get_meta('_ticket_created', true);
        if ($saved === 'yes') {
            $ticket = $this->service->find(['id_order' => $order->get_id()]);
            if (!empty($ticket)) {
                return $ticket[0];
            }
            return false;
        }
        // $items = $order->get_items();
        // foreach ($items as $item) {
        // }
        $order->set_meta_data([ '_ticket_created' => 'yes' ]);
        $order->save_meta_data();
    }
}