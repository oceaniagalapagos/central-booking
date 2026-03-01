<?php

use CentralBooking\Data\Constants\LogLevel;
use CentralBooking\Data\Constants\LogSource;
use CentralBooking\Data\Constants\PassengerConstants;
use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\Data\Constants\TypeOperation;
use CentralBooking\Data\Constants\TransportCustomeFieldConstants;
use CentralBooking\Data\Date;
use CentralBooking\Data\Location;
use CentralBooking\Data\MetaManager;
use CentralBooking\Data\Operator;
use CentralBooking\Data\Passenger;
use CentralBooking\Data\ProofPayment;
use CentralBooking\Data\Repository\CouponRepository;
use CentralBooking\Data\Repository\ResultSet;
use CentralBooking\Data\Repository\ResultSetInterface;
use CentralBooking\Data\Route;
use CentralBooking\Data\SecretKey;
use CentralBooking\Data\Serializer;
use CentralBooking\Data\Service;
use CentralBooking\Data\Services\ErrorService;
use CentralBooking\Data\Services\LocationService;
use CentralBooking\Data\Services\LogItem;
use CentralBooking\Data\Services\LogService;
use CentralBooking\Data\Services\OperatorService;
use CentralBooking\Data\Services\PassengerService;
use CentralBooking\Data\Services\RouteService;
use CentralBooking\Data\Services\ServiceService;
use CentralBooking\Data\Services\TemporalService;
use CentralBooking\Data\Services\TicketService;
use CentralBooking\Data\Services\TransportService;
use CentralBooking\Data\Services\ZoneService;
use CentralBooking\Data\Ticket;
use CentralBooking\Data\Time;
use CentralBooking\Data\Transport;
use CentralBooking\Data\Zone;

function git_temporal_service()
{
    if (!isset($GLOBALS['_git_temporal_service']) || !($GLOBALS['_git_temporal_service'] instanceof TemporalService)) {
        $GLOBALS['_git_temporal_service'] = new TemporalService();
    }
    return $GLOBALS['_git_temporal_service'];
}

function git_assign_coupon_to_operator(Operator $operator, WP_Post $coupon)
{
    return (new CouponRepository)->assignCouponToOperator($coupon, $operator);
}

function git_assign_url_brand_media_to_coupon(WP_Post $coupon, string $url_logo)
{
    if (filter_var($url_logo, FILTER_VALIDATE_URL)) {
        MetaManager::setMeta(MetaManager::COUPON, $coupon->ID, 'logo_sale', $url_logo);
    }
}

function git_recover_url_brand_media_from_coupon(WP_Post $coupon)
{
    $result = MetaManager::getMeta(MetaManager::COUPON, $coupon->ID, 'logo_sale');
    if ($result === null) {
        return CENTRAL_BOOKING_URL . '/assets/img/logo-placeholder.png';
    }
    return (string) $result;
}

function git_time_create(string $hhmmss = 'now')
{
    return new Time($hhmmss);
}

function git_operator_by_coupon(WP_Post $coupon)
{
    return OperatorService::getInstance()->findByCoupon($coupon);
}

function git_operators()
{
    return OperatorService::getInstance()->findAll();
}

function git_operator_save(Operator $operator)
{
    return OperatorService::getInstance()->save($operator);
}

function git_operator_by_id(int $id)
{
    return OperatorService::getInstance()->findById($id);
}

function git_routes_result_set(array $args = [])
{
    $limit = -1;
    $offset = 0;
    $orderBy = $args['order_by'] ?? 'id';
    $order = $args['order'] ?? 'ASC';
    if (isset($args['limit']) && is_int($args['limit'])) {
        $limit = intval($args['limit']);
    }
    if (isset($args['offset']) && is_int($args['offset'])) {
        $offset = intval($args['offset']);
    }
    return RouteService::getInstance()->find($args, $limit, $offset, $orderBy, $order);
}

function git_routes(array $args = [])
{
    $args['limit'] = -1;
    $args['offset'] = 0;
    return git_routes_result_set($args)->getItems();
}

function git_route_by_id(int $id)
{
    $result = git_routes(['id' => $id]);
    if (count($result) > 0) {
        return $result[0];
    }
    return null;
}

function git_route_save(Route|array $data)
{
    if (is_array($data)) {
        $ticket = git_route_create($data);
        return RouteService::getInstance()->save($ticket);
    }
    return RouteService::getInstance()->save($data);
}

function git_ticket_by_id(int $id)
{
    $result = git_tickets(['id' => $id]);
    if (count($result) > 0) {
        return $result[0];
    }
    return null;
}

function git_ticket_save(Ticket|array $data)
{
    $ticket = is_array($data) ? git_ticket_create($data) : $data;
    return TicketService::getInstance()->save($ticket);
}

function git_zone_by_name(string $name)
{
    $result = git_zones(['name' => $name]);
    if (count($result) > 0) {
        return $result[0];
    }
    return null;
}

/**
 * @param array{id:int,name:string,zone_id:int,zone:Zone,meta:array<string,mixed>} $data
 * @return Location
 */
function git_location_create(array $data = [])
{
    $location = new Location();
    $location->name = sanitize_text_field($data['name'] ?? '');
    $location->id = $data['id'] ?? 0;

    if (isset($data['zone_id'])) {
        $zone = git_zone_by_id($data['zone_id']);
        if ($zone !== null) {
            $location->setZone($zone);
        }
    } elseif (isset($data['zone']) && $data['zone'] instanceof Zone) {
        $location->setZone($data['zone']);
    }

    foreach ($data['meta'] ?? [] as $key => $value) {
        $location->setMeta($key, $value);
    }
    return $location;
}

function git_zone_by_id(int $id)
{
    $zones = git_zones(['id' => $id]);
    if ($zones === []) {
        return null;
    }
    return $zones[0];
}

function git_transports(array $args = [])
{
    $args['limit'] = -1;
    $args['offset'] = 0;
    return git_transports_result_set($args)->getItems();
}

function git_transport_by_id(int $id)
{
    $transports = git_transports(['id' => $id]);
    if (count($transports) > 0) {
        return $transports[0];
    }
    return null;
}

function git_transports_result_set(array $args = [])
{
    $limit = -1;
    $offset = 0;
    $order = $args['order'] ?? 'ASC';
    $orderBy = $args['order_by'] ?? 'id';
    if (isset($args['limit']) && is_int($args['limit'])) {
        $limit = intval($args['limit']);
    }
    if (isset($args['offset']) && is_int($args['offset'])) {
        $offset = intval($args['offset']);
    }
    if (isset($args['order'])) {
        unset($args['order']);
    }
    if (isset($args['order_by'])) {
        unset($args['order_by']);
    }
    return TransportService::getInstance()->find($args, $orderBy, $order, $limit, $offset);
}

function git_passenger_by_id(int $id)
{
    $passengers = git_passengers(['id' => $id]);
    if (count($passengers) > 0) {
        return $passengers[0];
    }
    return null;
}

function git_coupon_by_id(int $id)
{
    return (new CouponRepository)->find($id);
}

function git_coupons()
{
    return (new CouponRepository)->findAll();
}

function git_zones(array $args = [])
{
    $limit = -1;
    $offset = 0;
    $order = $args['order'] ?? 'ASC';
    $orderBy = $args['order_by'] ?? 'id';

    unset($args['limit'], $args['offset'], $args['order'], $args['order_by']);

    return ZoneService::getInstance()->find(
        $args,
        $orderBy,
        $order,
        $limit,
        $offset
    )->getItems();
}

/**
 * @param array{id:int,name:string,meta:array<string,mixed>} $data
 * @return Zone
 */
function git_zone_create(array $data = [])
{
    $zone = new Zone();
    $zone->id = (int) ($data['id'] ?? 0);
    $zone->name = sanitize_text_field($data['name'] ?? '');
    foreach ($data['meta'] ?? [] as $key => $value) {
        $zone->setMeta($key, $value);
    }
    return $zone;
}

function git_zone_save(Zone|array $data)
{
    if (is_array($data)) {
        $zone = git_zone_create($data);
        return ZoneService::getInstance()->save($zone);
    }
    return ZoneService::getInstance()->save($data);
}

function git_service_by_id(int $id)
{
    $services = git_services(['id' => $id]);
    if (count($services) > 0) {
        return $services[0];
    }
    return null;
}

function git_services(array $args = [])
{
    $args['limit'] = -1;
    $args['offset'] = 0;
    return git_services_result_set($args)->getItems();
}

function git_service_save(Service|array $data)
{
    if (is_array($data)) {
        $service = git_service_create($data);
        return ServiceService::getInstance()->save($service);
    }
    return ServiceService::getInstance()->save($data);
}

function git_services_result_set(array $args = [])
{
    $limit = (int) ($args['limit'] ?? -1);
    $offset = (int) ($args['offset'] ?? 0);
    $order = $args['order'] ?? 'ASC';
    $orderBy = $args['order_by'] ?? 'id';

    unset($args['limit'], $args['offset'], $args['order'], $args['order_by']);

    return ServiceService::getInstance()->find($args, $orderBy, $order, $limit, $offset);
}

function git_passengers(array $args = [])
{
    $args['limit'] = -1;
    $args['offset'] = 0;
    return git_passengers_result_set($args)->getItems();
}

function git_passengers_result_set(array $args = [])
{
    $limit = (int) ($args['limit'] ?? -1);
    $offset = (int) ($args['offset'] ?? 0);
    $order = $args['order'] ?? 'ASC';
    $orderBy = $args['order_by'] ?? 'id';

    unset($args['limit'], $args['offset'], $args['order'], $args['order_by']);

    return (new PassengerService)->find($args, limit: $limit, offset: $offset, order_by: $orderBy, order: $order);
}

function git_tickets(array $args = [])
{
    $args['limit'] = -1;
    $args['offset'] = 0;
    return git_tickets_result_set($args)->getItems();
}

/**
 * @param array{limit:int,offset:int,order_by:string,order:string} $args
 * @return ResultSetInterface<Ticket>
 */
function git_tickets_result_set(array $args = [])
{
    $limit = (int) ($args['limit'] ?? -1);
    $offset = (int) ($args['offset'] ?? 0);
    $order = $args['order'] ?? 'ASC';
    $orderBy = $args['order_by'] ?? 'id';

    unset($args['limit'], $args['offset'], $args['order'], $args['order_by']);

    return TicketService::getInstance()->find($args, $orderBy, $order, $limit, $offset);
}

/**
 * Creates a new Service instance from an array of data.
 * 
 * This function creates a Service object with sanitized data and handles
 * multiple ways of associating transports with the service. Transports can
 * be provided as existing IDs, Transport objects, or arrays of data to
 * create new Transport instances.
 * 
 * The function performs the following operations:
 * - Creates a new Service instance with basic properties (id, name, icon, price)
 * - Handles transport association through multiple methods:
 *   - `transport_ids`: Array of existing transport IDs to load and associate
 *   - `transports`: Array containing Transport objects or data arrays for new transports
 * - Sanitizes all input data for security
 * - Validates transport existence when using IDs
 * 
 * @param array{id:int,name:string,icon:string,price:int,transport_ids:int[],transports:array<Transport|array>,meta:array<string,mixed>} $data Service data with the following optional keys:
 *   - `id` `(int)`: Service identifier
 *   - `name` `(string)`: Service display name
 *   - `icon` `(string)`: URL to service icon image
 *   - `price` `(int)`: Service base price in cents
 *   - `transport_ids` `(int[])`: Array of existing transport IDs to associate
 *   - `transports` `(Transport[]|array[])`: Array of Transport objects or data arrays for transport creation
 *  - `meta` `(array<string,mixed>)`: Additional metadata for the service
 * 
 * @return Service The created Service instance with associated transports
 * 
 * @example
 * // Create service with existing transport IDs
 * $service = git_service_create([
 *     'name' => 'Ferry Service',
 *     'price' => 2500,
 *     'transport_ids' => [1, 2, 3]
 * ]);
 * 
 * @example
 * // Create service with mixed transport data
 * $service = git_service_create([
 *     'name' => 'Express Service',
 *     'transports' => [
 *         $existingTransport, // Transport object
 *         ['code' => 'BOAT01', 'capacity' => 50] // Data array
 *     ]
 * ]);
 * 
 * @since 1.0.0
 */
function git_service_create(array $data = [])
{
    $service = new Service();
    $service->id = (int) ($data['id'] ?? 0);
    $service->name = sanitize_text_field($data['name'] ?? '');
    $service->icon = sanitize_url($data['icon'] ?? '');
    $service->price = (int) ($data['price'] ?? 0);
    $transports = [];

    if (isset($data['transport_ids']) && is_array($data['transport_ids'])) {
        foreach ($data['transport_ids'] as $transport_id) {
            $transport = git_transport_by_id((int) $transport_id);
            if ($transport !== null) {
                $transports[] = $transport;
            }
        }
    } elseif (isset($data['transports']) && is_array($data['transports'])) {
        foreach ($data['transports'] as $transport_data) {
            if ($transport_data instanceof Transport) {
                $transports[] = $transport_data;
            } elseif (is_array($transport_data)) {
                $transport = git_transport_create($transport_data);
                $transports[] = $transport;
            }
        }
    }

    if ($transports !== []) {
        $service->setTransports($transports);
    }

    foreach ($data['meta'] ?? [] as $key => $value) {
        $service->setMeta($key, $value);
    }

    return $service;
}

/**
 * Creates a new Route instance from an array of data.
 * 
 * This function creates a Route object with flexible handling of location associations
 * and time settings. Locations can be provided as existing IDs, Location objects, or
 * arrays of data to create new Location instances. Times can be provided as formatted
 * strings or Time objects.
 * 
 * The function performs the following operations:
 * - Creates a new Route instance with basic properties (id, times, type)
 * - Handles origin and destiny association through multiple methods:
 *   - `origin_id`/`destiny_id`: IDs of existing locations to load and associate
 *   - `origin`/`destiny`: Location objects or data arrays for location creation
 * - Accepts times as formatted strings (HH:MM:SS) or Time objects
 * - Validates and converts transport type from string to enum
 * - Ensures location existence when using IDs
 * 
 * @param array{id?:int,departure_time?:string|Time,arrival_time?:string|Time,type?:string|TypeOperation,origin_id?:int,origin?:Location|array,destiny_id?:int,destiny?:Location|array,transports:array<Transport|array>,transports_id:int[],meta:array<string,mixed>} $data Route data with the following optional keys:
 *   - `id` (int): Route identifier
 *   - `departure_time` (string|Time): Departure time as 'HH:MM:SS' string or Time object
 *   - `arrival_time` (string|Time): Arrival time as 'HH:MM:SS' string or Time object
 *   - `type` (string|TransportConstants): Transport type as string or enum constant
 *   - `origin_id` (int): ID of existing origin location
 *   - `origin` (Location|array): Location object or data array for origin creation
 *   - `destiny_id` (int): ID of existing destiny location  
 *   - `destiny` (Location|array): Location object or data array for destiny creation
 *   - `meta` (array<string,mixed>): Additional metadata for the route
 * 
 * @return Route The created Route instance with associated locations and configured times
 * 
 * @example
 * // Create route with location IDs and string times
 * $route = git_route_create([
 *     'origin_id' => 1,
 *     'destiny_id' => 2,
 *     'departure_time' => '08:30:00',
 *     'arrival_time' => '10:15:00',
 *     'type' => 'marine'
 * ]);
 * 
 * @example
 * // Create route with mixed location data and Time objects
 * $route = git_route_create([
 *     'origin' => ['name' => 'Port A', 'zone' => 1],
 *     'destiny' => $existingLocation,
 *     'departure_time' => new Time('09:00:00'),
 *     'type' => TransportConstants::TERRESTRIAL
 * ]);
 * 
 * @since 1.0.0
 */
function git_route_create(array $data = [])
{
    $route = new Route();
    $route->id = (int) ($data['id'] ?? 0);
    $route->setDepartureTime(new Time($data['departure_time'] ?? '00:00:00'));

    if (isset($data['arrival_time'])) {
        if (is_string($data['arrival_time'])) {
            $route->setArrivalTime(new Time($data['arrival_time'] ?? '00:00:00'));
        } elseif ($data['arrival_time'] instanceof Time) {
            $route->setArrivalTime($data['arrival_time']);
        }
    }

    if (isset($data['departure_time'])) {
        if (is_string($data['departure_time'])) {
            $route->setDepartureTime(new Time($data['departure_time'] ?? '00:00:00'));
        } elseif ($data['departure_time'] instanceof Time) {
            $route->setDepartureTime($data['departure_time']);
        }
    }

    if (isset($data['type'])) {
        if (is_string($data['type'])) {
            $route->type = TypeOperation::fromSlug($data['type']) ??
                TypeOperation::NONE;
        } elseif ($data['type'] instanceof TypeOperation) {
            $route->type = $data['type'];
        }
    }

    if (isset($data['origin_id'])) {
        $origin = git_location_by_id((int) $data['origin_id']);
        if ($origin !== null) {
            $route->setOrigin($origin);
        }
    } elseif (isset($data['origin'])) {
        if ($data['origin'] instanceof Location) {
            $route->setOrigin($data['origin']);
        } elseif (is_array($data['origin'])) {
            $origin = git_location_create($data['origin']);
            $route->setOrigin($origin);
        }
    }

    if (isset($data['destiny_id'])) {
        $destiny = git_location_by_id((int) $data['destiny_id']);
        if ($destiny !== null) {
            $route->setDestiny($destiny);
        }
    } elseif (isset($data['destiny'])) {
        if ($data['destiny'] instanceof Location) {
            $route->setDestiny($data['destiny']);
        } elseif (is_array($data['destiny'])) {
            $destiny = git_location_create($data['destiny']);
            $route->setDestiny($destiny);
        }
    }

    $transports = [];

    if (isset($data['transports_id'])) {
        foreach ($data['transports_id'] as $id) {
            $transport = git_transport_by_id((int) $id);
            if ($transport === null) {
                continue;
            }
            $transports[] = $transport;
        }
    } elseif (isset($data['transports'])) {
        foreach ($data['transports'] as $transport) {
            if (is_array($transport)) {
                $transports[] = git_transport_create($transport);
            } elseif ($transport instanceof Transport) {
                $transports[] = $transport;
            }
        }
    }

    if (($transports === []) === false) {
        $route->setTransports($transports);
    }

    foreach ($data['meta'] ?? [] as $key => $value) {
        $route->setMeta($key, $value);
    }

    return $route;
}
/**
 * @param array{id:int,code:string,nicename:string,type:string|TypeOperation,capacity:int,photo_url:string,operator_id:int,working_days:array<string>,custom_field:array{field:string|TransportCustomeFieldConstants,content:string},alias:array<string>,crew:array<array{name:string,role:string,contact:string,license:string}>,routes_id:array<int>,routes:array<Route|array>,services_id:array<int>,services:array<Service|array>,meta:array<string,mixed>} $data
 * @return Transport
 */
function git_transport_create(array $data = [])
{
    $transport = new Transport();
    $operator = new Operator();
    $transport->id = intval($data['id'] ?? '0');
    $transport->code = sanitize_text_field($data['code'] ?? '');
    $transport->nicename = $data['nicename'] ?? '';
    $transport->type = TypeOperation::fromSlug($data['type'] ?? TypeOperation::NONE->slug());
    $transport->setCapacity(intval($data['capacity'] ?? '0'));
    $transport->setUrlPhoto(sanitize_url($data['photo_url'] ?? ''));
    $operator->setUser(new WP_User(intval($data['operator_id'] ?? '0')));
    $transport->setOperator($operator);
    if (!empty($data['photo_url'] ?? '')) {
        $transport->setUrlPhoto(sanitize_url($data['photo_url']));
    }
    if (isset($data['working_days']) && is_array($data['working_days'])) {
        $transport->setWorkingDays($data['working_days'] ?? []);
    }
    if (isset($data['custom_field']) && is_array($data['custom_field'])) {
        $transport->setCustomField(
            $data['custom_field']['content'] ?? '',
            TransportCustomeFieldConstants::from($data['custom_field']['field'] ?? TransportCustomeFieldConstants::TEXT->value),
        );
    }
    if (isset($data['alias']) && is_array($data['alias'])) {
        $alias = [];
        foreach ($data['alias'] as $al) {
            if (is_string($al)) {
                $alias[] = $al;
            }
        }
        $transport->setAlias($alias);
    }
    if (isset($data['crew']) && is_array($data['crew'])) {
        $crew = [];
        foreach ($data['crew'] as $crew_member_data) {
            $crew_member = [];
            $crew_member['name'] = sanitize_text_field($crew_member_data['name'] ?? '');
            $crew_member['role'] = sanitize_text_field($crew_member_data['role'] ?? '');
            $crew_member['contact'] = sanitize_text_field($crew_member_data['contact'] ?? '');
            $crew_member['license'] = sanitize_text_field($crew_member_data['license'] ?? '');
            $crew[] = $crew_member;
        }
        $transport->setCrew($crew);
    }

    $routes = [];
    if (isset($data['routes_id']) && is_array($data['routes_id'])) {
        foreach ($data['routes_id'] as $route_id) {
            $route = git_route_by_id(intval($route_id));
            if ($route !== null) {
                $routes[] = $route;
            }
        }
    } elseif (isset($data['routes']) && is_array($data['routes'])) {
        foreach ($data['routes'] as $route_data) {
            if ($route_data instanceof Route) {
                $routes[] = $route_data;
            } elseif (is_array($route_data)) {
                $route = git_route_create($route_data);
                $routes[] = $route;
            }
        }
    }
    $transport->setRoutes($routes);

    $services = [];
    if (isset($data['services_id']) && is_array($data['services_id'])) {
        foreach ($data['services_id'] as $service_id) {
            $service = git_service_by_id(intval($service_id));
            if ($service !== null) {
                $services[] = $service;
            }
        }
    } elseif (isset($data['services']) && is_array($data['services'])) {
        foreach ($data['services'] as $service_data) {
            if ($service_data instanceof Service) {
                $services[] = $service_data;
            } elseif (is_array($service_data)) {
                $service = git_service_create($service_data);
                $services[] = $service;
            }
        }
    }
    $transport->setServices($services);

    foreach ($data['meta'] ?? [] as $key => $value) {
        $transport->setMeta($key, $value);
    }

    return $transport;
}

/**
 * @param array{id:int,flexible:bool,total_amount:int,status:string|TicketStatus,order_id:int,order:WC_Order,coupon_id:int,coupon:WP_Post,client_id:int,client:WP_User,passengers:array<Passenger|array>,meta:array<string,mixed>} $data
 * @return Ticket
 */
function git_ticket_create(array $data)
{
    $ticket = new Ticket();
    $ticket->id = (int) ($data['id'] ?? 0);
    $ticket->flexible = (bool) $data['flexible'];
    $ticket->total_amount = intval($data['total_amount'] ?? 0);

    if (isset($data['status'])) {
        if ($data['status'] instanceof TicketStatus) {
            $ticket->status = $data['status'];
        } elseif (is_string($data['status'])) {
            $ticket->status = TicketStatus::fromSlug($data['status']) ?? TicketStatus::PENDING;
        }
    }

    if (isset($data['order'])) {
        $ticket->setOrder($data['order']);
    } elseif (isset($data['order_id'])) {
        $order = wc_get_order(intval($data['order_id']));
        if ($order instanceof WC_Order) {
            $ticket->setOrder($order);
        }
    }

    if (isset($data['coupon'])) {
        $ticket->setCoupon($data['coupon']);
    } elseif (isset($data['coupon_id'])) {
        $coupon = get_post(intval($data['coupon_id']));
        if ($coupon instanceof WP_Post) {
            $ticket->setCoupon($coupon);
        }
    }

    if (isset($data['client'])) {
        $ticket->setClient($data['client']);
    } elseif (isset($data['client_id'])) {
        $user = get_user_by('id', intval($data['client_id']));
        if ($user instanceof WP_User) {
            $ticket->setClient($user);
        }
    }

    $passengers = [];
    if (isset($data['passengers']) && is_array($data['passengers'])) {
        foreach ($data['passengers'] as $passenger_data) {
            if ($passenger_data instanceof Passenger) {
                $passengers[] = $passenger_data;
            } elseif (is_array($passenger_data)) {
                $passenger = git_passenger_create($passenger_data);
                $passengers[] = $passenger;
            }
        }
    }
    $ticket->setPassengers($passengers);

    foreach ($data['meta'] ?? [] as $key => $value) {
        $ticket->setMeta($key, $value);
    }

    return $ticket;
}

/**
 * @param array{id:int,name:string,nationality:string,type_document:string,data_document:string,type:string,served:bool,approved:bool,birthday:Date|string,date_trip:Date|string,route_id:int,route:Route,transport_id:int,transport:Transport,meta:array<string,mixed>} $data
 * @return Passenger
 */
function git_passenger_create(array $data = [])
{
    $passenger = new Passenger();
    $passenger->id = intval($data['id'] ?? 0);
    $passenger->name = sanitize_text_field($data['name'] ?? '');
    $passenger->nationality = sanitize_text_field($data['nationality'] ?? '');
    $passenger->setBirthday(new Date($data['birthday'] ?? '1970-01-01'));
    $passenger->setDateTrip(new Date($data['date_trip'] ?? '1970-01-01'));
    $passenger->typeDocument = sanitize_text_field($data['type_document'] ?? '');
    $passenger->dataDocument = sanitize_text_field($data['data_document'] ?? '');
    $passenger->type = PassengerConstants::tryFrom($data['type'] ?? PassengerConstants::STANDARD->value) ?? PassengerConstants::STANDARD;
    $passenger->served = isset($data['served']) ? (bool) $data['served'] : false;
    $passenger->approved = isset($data['approved']) ? (bool) $data['approved'] : false;

    if (isset($data['route'])) {
        if ($data['route'] instanceof Route) {
            $passenger->setRoute($data['route']);
        }
    } elseif (isset($data['route_id'])) {
        $route = git_route_by_id(intval($data['route_id']));
        if ($route !== null) {
            $passenger->setRoute($route);
        }
    }

    if (isset($data['transport'])) {
        if ($data['transport'] instanceof Transport) {
            $passenger->setTransport($data['transport']);
        }
    } elseif (isset($data['transport_id'])) {
        $transport = git_transport_by_id(intval($data['transport_id']));
        if ($transport !== null) {
            $passenger->setTransport($transport);
        }
    }

    foreach ($data['meta'] ?? [] as $key => $value) {
        $passenger->setMeta($key, $value);
    }

    return $passenger;
}

/**
 * @param array $args
 * @return ResultSetInterface<Location>
 */
function git_locations_result_set(array $args = [])
{
    $limit = -1;
    $offset = 0;
    $orderBy = $args['order_by'] ?? 'id';
    $order = $args['order'] ?? 'ASC';
    if (isset($args['limit']) && is_int($args['limit'])) {
        $limit = intval($args['limit']);
    }
    if (isset($args['offset']) && is_int($args['offset'])) {
        $offset = intval($args['offset']);
    }
    return LocationService::getInstance()->find($args, $orderBy, $order, $limit, $offset);
}

function git_locations(array $args = [])
{
    $args['limit'] = -1;
    $args['offset'] = 0;
    $args['order_by'] ??= 'id';
    $args['order'] ??= 'ASC';
    return git_locations_result_set($args)->getItems();
}

function git_location_by_id(int $id)
{
    $args = ['id' => $id];

    $locations = git_locations($args);

    if (count($locations) > 0) {
        return $locations[0];
    }

    return null;
}

function git_set_setting(string $key, mixed $value)
{
    MetaManager::setMeta(
        MetaManager::SETTING,
        0,
        $key,
        $value
    );
}

function git_get_setting(string $key, mixed $default = null)
{
    $value = MetaManager::getMeta(
        MetaManager::SETTING,
        0,
        $key
    );
    return $value ?? $default;
}

function git_get_map_setting(string $key, mixed $default = null)
{
    $references = explode('.', $key);
    $map = git_get_setting($references[0]) ?? null;
    foreach (array_slice($references, 1) as $ref) {
        if (is_array($map) && isset($map[$ref])) {
            $map = $map[$ref];
        } else {
            return $default;
        }
    }
    return $map;
}

function git_get_secret_key()
{
    return SecretKey::getInstance()->get();
}

function git_set_secret_key(string $key)
{
    return SecretKey::getInstance()->set($key);
}

function git_check_secret_key(string $key)
{
    return SecretKey::getInstance()->check($key);
}

/**
 * Transforma una cadena a cualquier tipo de dato.
 * @param string $value Cadena a parsear.
 * @return mixed Valor parseado, puede ser bool, int, float, null, string o array.
 */
function git_unserialize(string $value): mixed
{
    return Serializer::unserialize($value);
}

/**
 * Serializa un valor para almacenarlo en la base de datos.
 * Convierte tipos complejos a JSON, y maneja strings, booleans y nulls adecuadamente.
 * @param mixed $value Valor a serializar. Puede ser un string, boolean, null, int, float o array.
 * @return bool|string Retorna el valor serializado como string. En caso de un error, retorna false.
 */
function git_serialize(mixed $value): string
{
    return Serializer::serialize($value);
}

/**
 * @param array{filename:string,url:string,code:string,total_amount:int,date:Date} $data
 * @return ProofPayment
 */
function git_proof_payment_create(array $data = [])
{
    $proofPayment = new ProofPayment(
        filename: sanitize_text_field($data['filename'] ?? ''),
        url: sanitize_url($data['url'] ?? ''),
        code: sanitize_text_field($data['code'] ?? ''),
        amount: intval($data['total_amount'] ?? 0),
        date: new Date($data['date'] ?? date('Y-m-d')),
    );
    return $proofPayment;
}

/**
 * Checks the availability of a transport for a specific route on a given date.
 * 
 * This function performs multiple validations to determine if a transport can
 * accommodate a specific number of passengers on a given route and date:
 * - Validates that the transport exists and is available
 * - Verifies that the route exists and is compatible with the transport
 * - Calculates available capacity considering existing unserved passengers
 * 
 * @param Transport|int $transport Transport object or transport ID to verify
 * @param Route|int $route Route object or route ID to verify
 * @param Date $date_trip Trip date to check availability
 * @param int $passengers_count Number of passengers to accommodate (default 1)
 * 
 * @return bool|ErrorService Returns:
 *   - `true` if transport is available and has sufficient capacity
 *   - `false` if there is insufficient capacity
 *   - `ErrorService::TRANSPORT_NOT_FOUND` if transport does not exist
 *   - `ErrorService::TRANSPORT_NOT_AVAILABLE` if transport is not available on the date
 *   - `ErrorService::ROUTE_NOT_FOUND` if route does not exist
 *   - `ErrorService::TRANSPORT_DOES_NOT_TAKE_ROUTE` if transport cannot take the route
 * 
 * @since 1.0.0
 */
function git_transport_check_availability(Transport|int $transport, Route|int $route, Date $date_trip, int $passengers_count = 1)
{
    if ($passengers_count <= 0) {
        return true;
    }

    $transportObj = is_int($transport) ? git_transport_by_id($transport) : $transport;
    if ($transportObj === null) {
        return ErrorService::TRANSPORT_NOT_FOUND;
    }

    if ($transportObj->isAvailable($date_trip) === false) {
        return ErrorService::TRANSPORT_NOT_AVAILABLE;
    }

    $routeObj = is_int($route) ? git_route_by_id($route) : $route;
    if ($routeObj === null) {
        return ErrorService::ROUTE_NOT_FOUND;
    }

    if ($transportObj->takeRoute($routeObj) === false) {
        return ErrorService::TRANSPORT_DOES_NOT_TAKE_ROUTE;
    }

    $passengers = git_passengers([
        'id_route' => $routeObj->id,
        'id_transport' => $transportObj->id,
        'date_trip' => $date_trip->format('Y-m-d'),
        'served' => false,
    ]);

    $capacity = $transportObj->getCapacity();
    if (count($passengers) + $passengers_count > $capacity) {
        return false;
    }

    return true;
}

/**
 * Transfers a passenger to a new route and transport on a specified date.
 * 
 * This function handles the complete process of moving a passenger from their
 * current booking to a new route and transport combination. It validates all
 * entities exist, checks transport availability for the new assignment, and
 * updates the passenger's booking information if the transfer is possible.
 * 
 * The function performs the following operations:
 * - Validates passenger, route, and transport existence
 * - Checks transport availability for the specified date
 * - Updates passenger's route, transport, and trip date if transfer is successful
 * 
 * @param Passenger|int $passenger Passenger object or passenger ID to transfer
 * @param Route|int $route Route object or route ID for the new assignment
 * @param Transport|int $transport Transport object or transport ID for the new assignment
 * @param Date $date_trip New trip date for the passenger
 * 
 * @return bool|ErrorService Returns:
 *   - `true` if the passenger was successfully transferred
 *   - `ErrorService::PASSENGER_NOT_FOUND` if the passenger does not exist
 *   - `ErrorService::TICKET_NOT_FLEXIBLE` if the ticket is not flexible
 *   - `ErrorService::ROUTE_NOT_FOUND` if the route does not exist
 *   - `ErrorService::TRANSPORT_NOT_FOUND` if the transport does not exist
 *   - Any error code returned by `git_transport_check_availability()` if transfer is not possible
 * 
 * @see git_transport_check_availability() For availability validation details
 * @since 1.0.0
 */
function git_passenger_transfer(Passenger|int $passenger, Route|int $route, Transport|int $transport, Date $date_trip)
{
    $passengerObj = is_int($passenger) ? git_passenger_by_id($passenger) : $passenger;

    if ($passengerObj === null) {
        return ErrorService::PASSENGER_NOT_FOUND;
    }

    if ($passengerObj->getTicket()->flexible === false) {
        return ErrorService::TICKET_NOT_FLEXIBLE;
    }

    $routeObj = is_int($route) ? git_route_by_id($route) : $route;
    if ($routeObj === null) {
        return ErrorService::ROUTE_NOT_FOUND;
    }

    $transportObj = is_int($transport) ? git_transport_by_id($transport) : $transport;
    if ($transportObj === null) {
        return ErrorService::TRANSPORT_NOT_FOUND;
    }

    $is_availability = git_transport_check_availability(
        $transportObj,
        $routeObj,
        $date_trip,
        1
    );

    if ($is_availability === true) {
        $passengerObj->setRoute($routeObj);
        $passengerObj->setTransport($transportObj);
        $passengerObj->setDateTrip($date_trip);
        return true;
    }

    return $is_availability;
}

function git_passenger_save(Passenger|array $data)
{
    if (is_array($data)) {
        $passenger = git_passenger_create($data);
        return PassengerService::getInstance()->save($passenger);
    }
    return PassengerService::getInstance()->save($data);
}

function git_location_save(Location|array $data)
{
    if (is_array($data)) {
        $location = git_location_create($data);
        return LocationService::getInstance()->save($location);
    }
    return LocationService::getInstance()->save($data);
}

function git_log_create(LogSource $source, int $id_source, string $message, LogLevel $level)
{
    LogService::create_git_log(
        source: $source->slug(),
        id_source: $id_source,
        message: $message,
        level: $level->label(),
    );
}

/**
 * @param array{level:LogLevel,source:LogSource,date_from:Date,date_to:Date,id_source:int,limit:int,offset:int} $args
 * @return ResultSetInterface<LogItem>
 */
function git_log_result_set(array $args = [])
{
    if (!isset($args['level'], $args['source'])) {
        return new ResultSet(
            [],
            0,
            0,
            0,
            0,
            false,
            false,
            false
        );
    }
    $limit = $args['limit'] ?? -1;
    $offset = $args['offset'] ?? 0;
    $pagination = LogService::get_logs_with_pagination(
        level: isset($args['level']) ? $args['level']->label() : '',
        source: isset($args['source']) ? $args['source']->slug() : '',
        date_from: isset($args['date_from']) ? $args['date_from']->format('Y-m-d') : '',
        date_to: isset($args['date_to']) ? $args['date_to']->format('Y-m-d') : '',
        id_source: $args['id_source'] ?? 0,
        page: $offset > 0 && $limit > 0 ? intval($offset / $limit) + 1 : 1,
        per_page: $limit,
    );
    $logs = [];
    foreach ($pagination['logs'] as $log_data) {
        $log = new LogItem(
            intval($log_data->id),
            LogSource::from(sanitize_text_field($log_data->source)),
            LogLevel::from(sanitize_text_field($log_data->level)),
            is_null($log_data->id_source) ? 0 : intval($log_data->id_source),
            sanitize_text_field($log_data->message),
        );
        $logs[] = $log;
    }
    return new ResultSet(
        $logs,
        $pagination['pagination']['per_page'],
        $pagination['pagination']['current_page'],
        $pagination['pagination']['total_items'],
        $pagination['pagination']['total_pages'],
        count($pagination['logs']) > 0,
        $pagination['pagination']['has_previous'],
        $pagination['pagination']['has_next'],
    );
}

/**
 * Sets maintenance period for a transport, preventing new bookings during that time.
 * 
 * This function schedules a maintenance period for a transport by validating the
 * date range and ensuring no pending passengers have trips during the maintenance
 * window. If validation passes, it sets the maintenance dates on the transport.
 * 
 * The function performs the following validations:
 * - Validates that the transport exists
 * - Ensures the end date is not before the start date
 * - Checks for existing approved but unserved passengers during the maintenance period
 * 
 * @param Transport|int $transport Transport object or transport ID to set maintenance for
 * @param Date $dateStart Start date of the maintenance period
 * @param Date $dateEnd End date of the maintenance period
 * 
 * @return bool|ErrorService Returns:
 *   - `true` if maintenance period was successfully set
 *   - `ErrorService::TRANSPORT_NOT_FOUND` if the transport does not exist
 *   - `ErrorService::INVALID_DATE_RANGE` if end date is before start date
 *   - `ErrorService::PASSENGERS_PENDING_TRIPS` if there are pending passenger trips during the period
 * 
 * @since 1.0.0
 */
function git_transport_set_maintenance(Transport|int $transport, Date $dateStart, Date $dateEnd)
{
    $transportObj = is_int($transport) ? git_transport_by_id($transport) : $transport;
    if ($transportObj === null) {
        return ErrorService::TRANSPORT_NOT_FOUND;
    }
    if ($dateEnd->format('Y-m-d') < $dateStart->format('Y-m-d')) {
        return ErrorService::INVALID_DATE_RANGE;
    }
    $passengers = git_passengers([
        'id_transport' => $transportObj->id,
        'date_trip_from' => $dateStart->format('Y-m-d'),
        'date_trip_to' => $dateEnd->format('Y-m-d'),
        'served' => false,
        'approved' => true,
    ]);
    if (count($passengers) > 0) {
        return ErrorService::PASSENGERS_PENDING_TRIPS;
    }
    $transportObj->setMaintenanceDates($dateStart, $dateEnd);
    return true;
}

function git_transport_save(Transport|array $data)
{
    if (is_array($data)) {
        $transport = git_transport_create($data);
        return TransportService::getInstance()->save($transport);
    }
    return TransportService::getInstance()->save($data);
}