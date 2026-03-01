<?php
namespace CentralBooking\Utils\PackageData;

use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\Data\Ticket;

/**
 * @extends parent<Ticket>
 */
class TicketData implements PackageData
{
    /**
     * @param PassengerData[] $passengers
     */
    public function __construct(
        public readonly int $id_order = -1,
        public readonly int $id_coupon = -1,
        public readonly bool $flexible = false,
        public readonly int $total_amount = 0,
        public readonly string $status = '',
        public readonly array $passengers = [],
    ) {
    }

    public function get_data()
    {
        $ticket = new Ticket;
        $order = wc_get_order($this->id_order);
        $coupon = get_post($this->id_coupon);

        if ($coupon && $coupon->post_type === 'shop_coupon') {
            $ticket->setCoupon($coupon);
        }

        if ($order) {
            $ticket->setOrder($order);
        }

        $ticket->status =  TicketStatus::fromSlug($this->status);
        $ticket->flexible = $this->flexible;
        $ticket->total_amount = $this->total_amount;

        $ticket->setPassengers(
            array_map(
                function (PassengerData $passenger_data) use ($ticket) {
                    $passenger = $passenger_data->get_data();
                    $passenger->setTicket($ticket);
                    return $passenger;
                },
                $this->passengers
            )
        );

        return $ticket;
    }
}
