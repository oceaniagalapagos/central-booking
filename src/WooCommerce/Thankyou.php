<?php
namespace CentralBooking\WooCommerce;

use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\Data\Services\TicketService;

class Thankyou
{
    public function thankyou(int $order_id)
    {
        if (get_post_meta($order_id, '_order_saved', true) === 'yes') {
            return;
        }

        $order = wc_get_order($order_id);
        $items = $order->get_items();
        $data = [];

        foreach ($items as $item) {
            $data[] = unserialize($item->get_meta('_original_data'));
        }

        $coupon_id = -1;

        foreach ($order->get_coupons() as $coupon) {
            $coupon_id = wc_get_coupon_id_by_code($coupon->get_code());
        }

        foreach ($data as $ticket) {
            if (!($ticket instanceof CartItem)) {
                continue;
            }

            $data = git_ticket_create([
                'flexible' => $ticket->isFlexible(),
                'total_amount' => $ticket->calculatePrice() * 100,
                'status' => $coupon_id === -1 ? TicketStatus::PAYMENT : TicketStatus::PENDING,
            ]);

            $data->setOrder($order);

            if ($coupon_id !== -1) {
                $coupon = get_post($coupon_id);
                $data->setCoupon($coupon);
            }

            $passengers = [];

            foreach ($ticket->getPassengers() as $passenger) {
                $passenger_data = git_passenger_create([
                    'name' => $passenger->name,
                    'type' => $passenger->type,
                    'served' => false,
                    'approved' => $coupon_id === -1,
                    'birthday' => $passenger->birthday,
                    'date_trip' => $ticket->getDateTrip()->format(),
                    'nationality' => $passenger->nationality,
                    'type_document' => $passenger->typeDocument,
                    'data_document' => $passenger->dataDocument,
                ]);
                $route = git_route_by_id($ticket->getRoute()->id);
                $transport = git_transport_by_id($ticket->getTransport()->id);
                if ($route !== null) {
                    $passenger_data->setRoute($route);
                }
                if ($transport !== null) {
                    $passenger_data->setTransport($transport);
                }
                $passengers[] = $passenger_data;
            }

            $data->setPassengers($passengers);

            $response = (new TicketService)->save($data);

            if ($response !== null) {
                $item->delete_meta_data('_original_data');
                $item->save();
            }
        }

        update_post_meta($order_id, '_order_saved', 'yes');
    }
}