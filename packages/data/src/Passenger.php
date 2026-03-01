<?php
namespace CentralBooking\Data;

use CentralBooking\Data\Constants\PassengerConstants;
use CentralBooking\Data\Repository\LazyLoader;
use CentralBooking\Data\Services\ErrorService;

class Passenger
{
    public int $id = 0;
    public string $name = '';
    public string $nationality = '';
    public string $typeDocument = '';
    public string $dataDocument = '';
    public PassengerConstants $type = PassengerConstants::STANDARD;
    public bool $served = false;
    public bool $approved = false;

    private Date $birthday;
    private Date $dateTrip;
    private Ticket $ticket;
    private Route $route;
    private Transport $transport;
    private array $metadata = [];

    public function getMeta(string $key)
    {
        if (!isset($this->metadata[$key])) {
            $metaValue = MetaManager::getMeta(
                MetaManager::PASSENGER,
                $this->id,
                $key
            );
            $this->metadata[$key] = $metaValue;
        }
        return $this->metadata[$key] ?? null;
    }

    public function setMeta(string $key, mixed $value)
    {
        $this->metadata[$key] = $value;
    }

    public function saveMeta()
    {
        MetaManager::setMetadata(
            MetaManager::PASSENGER,
            $this->id,
            $this->metadata
        );
    }

    public function getTicket()
    {
        if (!isset($this->ticket)) {
            $this->ticket = LazyLoader::loadTicketByPassenger($this);
        }
        return $this->ticket;
    }

    public function setTicket(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function getRoute()
    {
        if (!isset($this->route)) {
            $this->route = LazyLoader::loadRouteByPassenger($this);
        }
        return $this->route;
    }

    public function setRoute(Route $route)
    {
        $this->route = $route;
    }

    public function getTransport()
    {
        if (!isset($this->transport)) {
            $this->transport = LazyLoader::loadTransportByPassenger($this);
        }
        return $this->transport;
    }

    public function setTransport(Transport $transport)
    {
        $this->transport = $transport;
    }

    public function getBirthday()
    {
        return $this->birthday;
    }

    public function setBirthday(Date $birthday)
    {
        $this->birthday = $birthday;
    }

    public function getDateTrip()
    {
        return $this->dateTrip;
    }

    public function setDateTrip(Date $dateTrip)
    {
        $this->dateTrip = $dateTrip;
    }

    public function transfer(Transport $transport, Route $route, Date $dateTrip)
    {
        if ($this->canTransfer() === false) {
            return false;
        }
        if ($this->getTicket()->flexible === false) {
            return ErrorService::TICKET_NOT_FLEXIBLE;
        }

        $isAvailability = $transport->checkAvaility(
            $route,
            $dateTrip,
            1
        );

        if ($isAvailability === true) {
            $this->setRoute($route);
            $this->setDateTrip($dateTrip);
            $this->setTransport($transport);
            return true;
        }

        return $isAvailability;
    }

    public function canTransfer()
    {
        if ($this->getTicket()->flexible === false) {
            return false;
        }
        return $this->approved === true && $this->served === false;

        // $canTransfer = apply_filters('central_booking_passenger_can_transfer', $this->approved === true && $this->served === false, $this);

        // return (bool) $canTransfer;
    }

    public function save()
    {
        git_passenger_save($this);
    }
}
