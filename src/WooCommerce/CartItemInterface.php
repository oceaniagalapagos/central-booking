<?php
namespace CentralBooking\WooCommerce;

use CentralBooking\Data\Date;
use CentralBooking\Data\Route;
use CentralBooking\Data\Transport;
use WC_Product;

interface CartItemInterface
{
    /**
     * @return WC_Product
     */
    public function getProduct();
    /**
     * @return array{kid:int,rpm:int,standard:int,extra:int}
     */
    public function getPax();
    /**
     * @return bool
     */
    public function isFlexible();
    /**
     * @return Transport
     */
    public function getTransport();
    /**
     * @return Route
     */
    public function getRoute();
    /**
     * @return Date
     */
    public function getDateTrip();
    /**
     * @return int|float
     */
    public function calculatePrice();
}