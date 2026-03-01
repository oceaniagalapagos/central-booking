<?php
namespace CentralBooking\Data\Services;

use CentralBooking\Data\ORM\ORMInterface;
use CentralBooking\Data\ORM\TicketORM;
use CentralBooking\Data\Repository\PassengerRepository;
use CentralBooking\Data\Repository\TicketRepository;
use CentralBooking\Data\Ticket;
use Exception;

final class TicketService
{
    private PassengerRepository $passengerRepository;
    private TicketRepository $ticketRepository;
    private ORMInterface $ormTicket;
    private static ?TicketService $instance = null;

    /**
     * @return TicketService
     */
    public static function getInstance(): TicketService
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $wpdb = $GLOBALS['wpdb'];
        if ($wpdb) {
            $this->ticketRepository = new TicketRepository(
                $wpdb
            );
            $this->passengerRepository = new PassengerRepository(
                $wpdb
            );
            $this->ormTicket = new TicketORM();
        } else {
            throw new Exception('Error en la variable global wpdb');
        }
    }

    public function save(Ticket $ticket)
    {
        if ($ticket->total_amount < 0) {
            return null;
        }
        $ticketSaved = $this->ticketRepository->save($ticket);
        if ($ticketSaved !== null) {
            foreach ($ticket->getPassengers() as $passenger) {
                $passenger->setTicket($ticketSaved);
                $this->passengerRepository->save($passenger);
            }
        }
        return $ticketSaved;
    }

    public function find(
        array $args = [],
        string $orderBy = 'id',
        string $order = 'ASC',
        int $limit = -1,
        int $offset = 0,
    ) {
        return $this->ticketRepository->find(
            $this->ormTicket,
            $args,
            $orderBy,
            $order,
            $limit,
            $offset
        );
    }
}
