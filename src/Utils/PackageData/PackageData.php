<?php
namespace CentralBooking\Utils\PackageData;

/**
 * @template T
 */
interface PackageData
{
    /**
     * @return T
     */
    public function get_data();
}
