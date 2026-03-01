<?php
namespace CentralBooking\Utils\Senders;

use CentralBooking\Admin\Setting\SettingsKeys;
use CentralBooking\Data\Ticket;
use CentralBooking\Placeholders\PlaceholderEngineTicket;

class EmailTicketSender implements TicketSender
{
    public function send(Ticket $ticket)
    {
        $url = get_site_url();
        $parsed = parse_url($url);
        $subfix = '@' . ($parsed['host'] ?? $url);
        $order = $ticket->getOrder();
        $title = git_get_setting(SettingsKeys::NOTIFICATION_CHECKOUT_EMAIL_TITLE, "Central Reservas - Ticket # {$ticket->id}");
        $sender = git_get_setting(SettingsKeys::NOTIFICATION_CHECKOUT_EMAIL_SENDER, 'admin');
        return wp_mail(
            $order->get_billing_email(),
            $title,
            $this->create_message($ticket),
            [
                'Content-Type: text/html; charset=UTF-8',
                "From: {$title} <{$sender}{$subfix}>"
            ]
        );
    }

    private function create_message(Ticket $ticket)
    {
        $placeholder_engine = new PlaceholderEngineTicket($ticket);
        $content = git_get_setting(SettingsKeys::NOTIFICATION_CHECKOUT_EMAIL_CONTENT, "");
        return $placeholder_engine->process($content);
    }
}