<?php
namespace CentralBooking\Data\Repository;

use CentralBooking\Data\Location;
use CentralBooking\Data\MetaManager;
use CentralBooking\Data\Operator;
use CentralBooking\Data\Passenger;
use CentralBooking\Data\Route;
use CentralBooking\Data\Service;
use CentralBooking\Data\Ticket;
use CentralBooking\Data\Transport;
use CentralBooking\Data\Zone;
use WP_User;

final class LazyLoader
{
    private function __construct()
    {
    }

    public static function loadTransportsByRoute(Route $route)
    {
        global $wpdb;
        $table = DatabaseTable::TABLE_ROUTES_TRANSPORTS->value;
        $sql = "SELECT id_transport FROM {$wpdb->prefix}{$table} WHERE id_route = %d";
        $sql = $wpdb->prepare($sql, $route->id);
        $service_ids = $wpdb->get_col($sql);
        if (!empty($service_ids)) {
            $transports = [];
            foreach ($service_ids as $service_id) {
                $transport = git_transport_by_id((int) $service_id);
                if ($transport !== null) {
                    $transports[] = $transport;
                }
            }
            return $transports;
        }
        return [];
    }

    public static function loadOriginByRoute(Route $route)
    {
        global $wpdb;
        $table = DatabaseTable::TABLE_ROUTES->value;
        $sql = "SELECT id_origin FROM {$wpdb->prefix}{$table} WHERE id = %d";
        $sql = $wpdb->prepare($sql, $route->id);
        $location_id = $wpdb->get_var($sql);
        if ($location_id) {
            $location = git_location_by_id((int) $location_id);
            if ($location !== null) {
                return $location;
            }
        }
        return new Location();
    }

    public static function loadDestinyByRoute(Route $route)
    {
        global $wpdb;
        $table = DatabaseTable::TABLE_ROUTES->value;
        $sql = "SELECT id_destiny FROM {$wpdb->prefix}{$table} WHERE id = %d";
        $sql = $wpdb->prepare($sql, $route->id);
        $location_id = $wpdb->get_var($sql);
        if ($location_id) {
            $location = git_location_by_id((int) $location_id);
            if ($location !== null) {
                return $location;
            }
        }
        return new Location();
    }

    public static function loadZoneByLocation(Location $location)
    {
        global $wpdb;
        $sql = "SELECT meta_value FROM {$wpdb->prefix}git_meta WHERE meta_type = %s AND meta_key = %s AND meta_id = %d";
        $sql = $wpdb->prepare(
            $sql,
            MetaManager::LOCATION,
            'parent_zone',
            $location->id
        );
        $zone_id = $wpdb->get_var($sql);
        if ($zone_id) {
            $zone = git_zone_by_id((int) $zone_id);
            if ($zone !== null) {
                return $zone;
            }
        }
        return new Zone();
    }

    public static function loadLocationsByZone(Zone $zone)
    {
        global $wpdb;
        $sql = "SELECT meta_id FROM {$wpdb->prefix}git_meta WHERE meta_type = %s AND meta_key = %s AND meta_value = %d";
        $sql = $wpdb->prepare(
            $sql,
            MetaManager::LOCATION,
            'parent_zone',
            $zone->id
        );
        $location_ids = $wpdb->get_col($sql);
        if (!empty($location_ids)) {
            $locations = [];
            foreach ($location_ids as $location_id) {
                $location = git_location_by_id((int) $location_id);
                if ($location !== null) {
                    $locations[] = $location;
                }
            }
            return $locations;
        }
        return [];
    }

    public static function loadUserByOperator(Operator $operator)
    {
        return new WP_User();
    }

    public static function loadCouponsByOperator(Operator $operator)
    {
        $repostiry = new CouponRepository();
        return $repostiry->findCouponsByOperator($operator);
    }

    public static function loadTransportsByOperator(Operator $operator)
    {
        return git_transports(['id_operator' => $operator->getUser()->ID]);
    }

    public static function loadTicketByPassenger(Passenger $passenger)
    {
        global $wpdb;
        $table = DatabaseTable::TABLE_PASSENGERS->value;
        $sql = "SELECT id_ticket FROM {$wpdb->prefix}{$table} WHERE id = %d";
        $sql = $wpdb->prepare(
            $sql,
            $passenger->id
        );
        $ticket_id = $wpdb->get_var($sql);
        if ($ticket_id) {
            $ticket = git_ticket_by_id((int) $ticket_id);
            if ($ticket !== null) {
                return $ticket;
            }
        }
        return new Ticket();
    }

    public static function loadRouteByPassenger(Passenger $passenger)
    {
        global $wpdb;
        $table = DatabaseTable::TABLE_PASSENGERS->value;
        $sql = "SELECT id_route FROM {$wpdb->prefix}{$table} WHERE id = %d";
        $sql = $wpdb->prepare(
            $sql,
            $passenger->id
        );
        $route_id = $wpdb->get_var($sql);
        if ($route_id) {
            $route = git_route_by_id((int) $route_id);
            if ($route !== null) {
                return $route;
            }
        }
        return new Route();
    }

    public static function loadTransportByPassenger(Passenger $passenger)
    {
        global $wpdb;
        $table = DatabaseTable::TABLE_PASSENGERS->value;
        $sql = "SELECT id_transport FROM {$wpdb->prefix}{$table} WHERE id = %d";
        $sql = $wpdb->prepare(
            $sql,
            $passenger->id
        );
        $transport_id = $wpdb->get_var($sql);
        if ($transport_id) {
            $transport = git_transport_by_id((int) $transport_id);
            if ($transport !== null) {
                return $transport;
            }
        }
        return new Transport();
    }

    public static function loadPassengersByTicket(Ticket $ticket)
    {
        global $wpdb;
        $table = DatabaseTable::TABLE_PASSENGERS->value;
        $sql = "SELECT id FROM {$wpdb->prefix}{$table} WHERE id_ticket = %d";
        $sql = $wpdb->prepare($sql, $ticket->id);
        $passenger_ids = $wpdb->get_col($sql);
        $passengers = [];
        if (!empty($passenger_ids)) {
            foreach ($passenger_ids as $passenger_id) {
                $passenger = git_passenger_by_id((int) $passenger_id);
                if ($passenger !== null) {
                    $passengers[] = $passenger;
                }
            }
            return $passengers;
        }
        return [];
    }

    public static function loadServicesByTransport(Transport $transport)
    {
        global $wpdb;
        $table = DatabaseTable::TABLE_TRANSPORTS_SERVICES->value;
        $sql = "SELECT id_service FROM {$wpdb->prefix}{$table} WHERE id_transport = %d";
        $sql = $wpdb->prepare($sql, $transport->id);
        $service_ids = $wpdb->get_col($sql);
        if (!empty($service_ids)) {
            $services = [];
            foreach ($service_ids as $service_id) {
                $service = git_service_by_id((int) $service_id);
                if ($service !== null) {
                    $services[] = $service;
                }
            }
            return $services;
        }
        return [];
    }

    public static function loadTransportsByService(Service $service)
    {
        if ($service <= 0) {
            return [];
        }
        global $wpdb;
        $table = DatabaseTable::TABLE_TRANSPORTS_SERVICES->value;
        $sql = "SELECT id_transport FROM {$wpdb->prefix}{$table} WHERE id_service = %d";
        $sql = $wpdb->prepare($sql, $service->id);
        $service_ids = $wpdb->get_col($sql);
        if (!empty($service_ids)) {
            $transports = [];
            foreach ($service_ids as $service_id) {
                $service = git_transport_by_id((int) $service_id);
                if ($service !== null) {
                    $transports[] = $service;
                }
            }
            return $transports;
        }
        return [];
    }

    public static function loadOrderByTicket(Ticket $ticket)
    {
        global $wpdb;
        $table = DatabaseTable::TABLE_TICKETS->value;
        $sql = "SELECT id_order FROM {$wpdb->prefix}{$table} WHERE id = %d";
        $sql = $wpdb->prepare($sql, $ticket->id);
        $result = $wpdb->get_var($sql);
        $order = wc_get_order((int) $result);
        if ($order === false) {
            return null;
        }
        return $order;
    }

    public static function loadCouponByTicket(Ticket $ticket)
    {
        global $wpdb;
        $table = DatabaseTable::TABLE_TICKETS->value;
        $sql = "SELECT id_coupon FROM {$wpdb->prefix}{$table} WHERE id = %d";
        $sql = $wpdb->prepare($sql, $ticket->id);
        $result = $wpdb->get_var($sql);
        if ($result !== null && $result != -1) {
            return get_post((int) $result);
        }
        return null;
    }

    public static function loadRoutesByTransport(Transport $transport)
    {
        global $wpdb;
        $table = DatabaseTable::TABLE_ROUTES_TRANSPORTS->value;
        $sql = "SELECT id_route FROM {$wpdb->prefix}{$table} WHERE id_transport = %d";
        $sql = $wpdb->prepare($sql, $transport->id);
        $routes_id = $wpdb->get_col($sql);
        if (!empty($routes_id)) {
            $routes = [];
            foreach ($routes_id as $route_id) {
                $transport = git_route_by_id((int) $route_id);
                if ($transport !== null) {
                    $routes[] = $transport;
                }
            }
            return $routes;
        }
        return [];
    }

    public static function loadOperatorByTransport(Transport $transport)
    {
        global $wpdb;
        $table = DatabaseTable::TABLE_TRANSPORTS->value;
        $sql = "SELECT id_operator FROM {$wpdb->prefix}{$table} WHERE id = %d";
        $sql = $wpdb->prepare($sql, $transport->id);
        $operator_id = $wpdb->get_var($sql);
        if ($operator_id) {
            $operator = git_operator_by_id((int) $operator_id);
            if ($operator !== null) {
                return $operator;
            }
        }
        return new Operator();
    }
}
