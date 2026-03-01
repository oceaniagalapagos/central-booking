<?php
namespace CentralBooking\WooCommerce;

use CentralBooking\Data\Constants\PriceExtraConstants;
use CentralBooking\Data\Constants\PassengerConstants;
use CentralBooking\Data\Date;
use CentralBooking\Data\Route;
use CentralBooking\Data\Transport;
use WC_Product;

class CartItem implements CartItemInterface
{
    private WC_Product $product;
    private Route $route;
    private Transport $transport;
    private Date $dateTrip;
    /**
     * @var array<CartPassenger>
     */
    private array $passengers;
    private bool $flexible;
    /**
     * @var array{kid:int,rpm:int,standard:int,extra:int}
     */
    private array $pax = ['kid' => 0, 'rpm' => 0, 'standard' => 0, 'extra' => 0,];

    private function __construct()
    {
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getTransport()
    {
        return $this->transport;
    }

    public function getPassengers()
    {
        return $this->passengers;
    }

    public function getDateTrip()
    {
        return $this->dateTrip;
    }

    public function getPax()
    {
        return [
            PassengerConstants::KID->value => intval($this->pax['kid']),
            PassengerConstants::RPM->value => intval($this->pax['rpm']),
            PriceExtraConstants::EXTRA->value => intval($this->pax['extra']),
            PassengerConstants::STANDARD->value => intval($this->pax['standard']),
        ];
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function isFlexible()
    {
        return $this->flexible;
    }

    public function calculatePrice()
    {
        $calculate = new CalculateTicketPrice();
        return $calculate->calculate($this);
    }

    /**
     * @param Route $route
     * @param Transport $transport
     * @param Date $dateTrip
     * @param WC_Product $product
     * @param bool $flexible
     * @param array<CartPassenger> $passengers
     * @param array{kid:int,rpm:int,standard:int,extra:int} $pax
     * @return CartItem
     */
    public static function create(
        Route $route,
        Transport $transport,
        Date $dateTrip,
        WC_Product $product,
        bool $flexible = false,
        array $passengers = [],
        array $pax = []
    ) {
        $intance = new self();
        $intance->route = $route;
        $intance->product = $product;
        $intance->transport = $transport;
        $intance->dateTrip = $dateTrip;
        $intance->flexible = $flexible;
        $intance->passengers = $passengers;
        $intance->pax = [
            PassengerConstants::KID->value => intval($pax['kid'] ?? 0),
            PassengerConstants::RPM->value => intval($pax['rpm'] ?? 0),
            PriceExtraConstants::EXTRA->value => intval($pax['extra'] ?? 0),
            PassengerConstants::STANDARD->value => intval($pax['standard'] ?? 0),
        ];
        return $intance;
    }

    public function getHash()
    {
        $product = [
            'key' => git_get_secret_key(),
            'date_created'=> date('Y-m-d H:i:s'),
            'route' => $this->route->id,
            'transport' => $this->transport->id,
            'date_trip' => $this->dateTrip->format(),
            'flexible' => $this->flexible,
            'pax' => $this->pax,
            'passengers' => array_map(
                fn(CartPassenger $passenger) => [
                    'type' => $passenger->type,
                    'name' => $passenger->name,
                    'birthday' => $passenger->birthday,
                    'nationality' => $passenger->nationality,
                    'type_document' => $passenger->typeDocument,
                    'data_document' => $passenger->dataDocument,
                ],
                $this->getPassengers()
            ),
        ];

        return hash('sha256', (string) json_encode($product));
    }
}