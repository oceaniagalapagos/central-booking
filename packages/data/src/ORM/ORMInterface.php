<?php
namespace CentralBooking\Data\ORM;

/**
 * @template T
 */
interface ORMInterface
{
    /**
     * @param array $data
     * @return T
     */
    public function mapper(array $data);
}