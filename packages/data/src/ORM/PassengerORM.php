<?php
namespace CentralBooking\Data\ORM;

use CentralBooking\Data\Constants\PassengerConstants;
use CentralBooking\Data\Date;
use CentralBooking\Data\Passenger;

/**
 * @implements ORMInterface<Passenger>
 */
final class PassengerORM implements ORMInterface
{
    public function mapper(array $data)
    {
        $passenger = new Passenger();
        $passenger->id = (int)($data['id'] ?? 0);
        $passenger->name = (string)($data['name'] ?? '');
        $passenger->nationality = (string)($data['nationality'] ?? '');
        $passenger->typeDocument = (string)($data['type_document'] ?? '');
        $passenger->dataDocument = (string)($data['data_document'] ?? '');
        $passenger->served = $data['served'] === '1';
        $passenger->approved = $data['approved'] === '1';
        $passenger->type = PassengerConstants::tryFrom($data['type']) ?? PassengerConstants::STANDARD;
        $passenger->setBirthday(isset($data['birthday']) ? new Date($data['birthday']) : Date::today());
        $passenger->setDateTrip(isset($data['date_trip']) ? new Date($data['date_trip']) : Date::today());
        return $passenger;
    }
}
