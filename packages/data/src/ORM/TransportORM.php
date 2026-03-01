<?php
namespace CentralBooking\Data\ORM;

use CentralBooking\Data\Constants\TypeOperation;
use CentralBooking\Data\Transport;

/**
 * @implements ORMInterface<Transport>
 */
final class TransportORM implements ORMInterface
{
    public function mapper(array $data)
    {
        $transport = new Transport();
        $transport->id = (int) ($data['id'] ?? 0);
        $transport->code = (string) ($data['code'] ?? '');
        $transport->nicename = (string) ($data['nicename'] ?? '');
        $transport->type = TypeOperation::fromSlug(($data['type'] ?? '')) ?? TypeOperation::NONE;
        return $transport;
    }
}
