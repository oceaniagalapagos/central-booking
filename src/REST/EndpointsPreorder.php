<?php
namespace CentralBooking\REST;

use CentralBooking\Data\Passenger;
use CentralBooking\Data\Route;
use WP_REST_Request;
use WP_REST_Response;

class EndpointsPreorder
{
    public function init_endpoints()
    {
        RegisterRoute::register(
            'tickets',
            'POST',
            [$this, 'post_ticket']
        );
    }

    public function get_routes(WP_REST_Request $request)
    {
        return new WP_REST_Response(array_map(
            fn(Route $route) => [
                'id' => $route->id,
                'type' => $route->type->slug(),
                'origin' => [
                    'id' => $route->getOrigin()->id,
                    'name' => $route->getOrigin()->name,
                ],
                'destiny' => [
                    'id' => $route->getDestiny()->id,
                    'name' => $route->getDestiny()->name,
                ],
                'departure_time' => $route->getDepartureTime()->format(),
                'arrival_time' => $route->getArrivalTime()->format(),
            ],
            git_routes()
        ), 200);
    }

    public function post_ticket(WP_REST_Request $request)
    {
        if ($this->validate_request($request) === false) {
            return new WP_REST_Response(['message' => 'Authorization failed'], 403);
        }

        $data = $request->get_json_params();
        unset($data['id'], $data['coupon_id'], $data['order_id'], $data['client']);
        $ticket = git_ticket_create($data);
        $saved = $ticket->save();
        if ($saved) {
            return new WP_REST_Response([
                'id' => $ticket->id,
                'status' => $ticket->status->slug(),
                'flexible' => $ticket->flexible,
                'total_amount' => $ticket->total_amount,
                'passengers' => array_map(
                    fn(Passenger $passenger) => [
                        'name' => $passenger->name,
                        'nationality' => $passenger->nationality,
                        'type_document' => $passenger->typeDocument,
                        'data_document' => $passenger->dataDocument,
                    ],
                    $ticket->getPassengers()
                ),
            ], 200);
        }
        return new WP_REST_Response(['message' => 'Hubo un error al guardar el ticket'], 400);
    }

    private function validate_request(WP_REST_Request $request)
    {
        $header = $request->get_header('authorization');
        if (empty($header) === true) {
            return false;
        }
        $token = substr($header, 7);
        return git_check_api_key($token);
    }
}