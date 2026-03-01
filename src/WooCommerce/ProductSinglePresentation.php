<?php
namespace CentralBooking\WooCommerce;

final class ProductSinglePresentation
{
    /**
     * @param int $id_transport
     * @param int $id_route
     * @param string $date_trip
     * @param int $product_id
     * @param bool $flexible
     * @param array{type:string,name:string,nationality:string,type_document:string,data_document:string,birthday:string}[] $passengers
     * @param array{kid:int,rpm:int,extra:int,standard:int} $pax
     * @return bool|string
     */
    public function addToCart(int $id_transport, int $id_route, string $date_trip, int $product_id, bool $flexible, array $passengers, array $pax)
    {
        // echo json_encode([
        //     'id_transport' => $id_transport,
        //     'id_route' => $id_route,
        //     'date_trip' => $date_trip,
        //     'product_id' => $product_id,
        //     'flexible' => $flexible,
        // ]);
        // exit;
        $product = wc_get_product($product_id);
        $route = git_route_by_id($id_route);
        $transport = git_transport_by_id($id_transport);
        $date = git_date_create($date_trip);
        $passengersArray = [];
        foreach ($passengers as $passengerData) {
            $passenger = new CartPassenger;
            $passenger->type = $passengerData['type'];
            $passenger->name = $passengerData['name'];
            $passenger->nationality = $passengerData['nationality'];
            $passenger->typeDocument = $passengerData['type_document'];
            $passenger->dataDocument = $passengerData['data_document'];
            $passenger->birthday = $passengerData['birthday'];
            $passengersArray[] = $passenger;
        }
        $cartItem = CartItem::create(
            $route,
            $transport,
            $date,
            $product,
            $flexible,
            $passengersArray,
            $pax
        );
        $added = WC()->cart->add_to_cart(
            $product_id,
            1,
            0,
            [],
            [
                'cart_ticket' => $cartItem,
                'hash' => $cartItem->getHash(),
            ],
        );
        return is_string($added);
    }
}