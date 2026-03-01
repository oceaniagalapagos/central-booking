<?php
namespace CentralBooking\Utils\Senders;

use CentralBooking\Data\Ticket;

interface TicketSender
{
    public function send(Ticket $ticket);
}