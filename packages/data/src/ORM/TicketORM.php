<?php
namespace CentralBooking\Data\ORM;

use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\Data\Ticket;
use WP_User;

/**
 * @implements ORMInterface<Ticket>
 */
final class TicketORM implements ORMInterface
{
    public function mapper(array $data)
    {
        $ticket = new Ticket();
        $ticket->id = (int) ($data['id'] ?? 0);
        $ticket->total_amount = (int) ($data['total_amount'] ?? 0);
        $ticket->flexible = $data['flexible'] === '1';
        $ticket->status = TicketStatus::fromSlug($data['status']) ?? TicketStatus::PENDING;
        $ticket->setClient($this->get_user($data['id_client']));
        if ($data['id_coupon']) {
            $ticket->setCoupon(get_post((int) $data['id_coupon']));
        }
        return $ticket;
    }

    private function get_user(?int $user_id): ?WP_User
    {
        if ($user_id === null) {
            return null;
        }
        $user = get_user($user_id);
        if ($user instanceof WP_User) {
            return $user;
        }
        return null;
    }
}
