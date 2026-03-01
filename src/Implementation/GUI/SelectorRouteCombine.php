<?php
namespace CentralBooking\Implementation\GUI;

use CentralBooking\Data\Constants\UserRole;
use CentralBooking\Data\Route;
use CentralBooking\Data\Transport;

final class SelectorRouteCombine
{
    /**
     * @var array<Transport>
     */
    private array $transports;
    /**
     * @var array<Route>
     */
    private array $routes;
    private string $id;

    public function __construct()
    {
        $this->id = rand();
        wp_enqueue_script(
            'script_route_combine_selector',
            CENTRAL_BOOKING_URL . '/assets/js/route-selector-script.js',
            [],
            time(),
        );
    }

    public function get_transport_select(string $name = 'transport')
    {
        $select_component = git_select_field(['name' => $name]);
        $select_component->setRequired(true);
        $select_component->attributes->set('target', "selector_route_transport_$this->id");
        $select_component->addOption('Seleccione...', '');
        foreach ($this->get_transport_data() as $transport) {
            $class = array_map(fn($route) => "show_if_origin_{$route['origin']}_destiny_{$route['destiny']}_time_{$route['time']}", $transport['routes']);
            $select_component->addOption(
                $transport['nicename'],
                $transport['id'],
                false,
                ['class' => $class]
            );
        }
        return $select_component;
    }

    public function get_origin_select(string $name = 'origin')
    {
        $select_component = git_select_field(['name' => $name]);
        $select_component->setRequired(true);
        $select_component->attributes->set('target', "selector_route_origin_$this->id");
        $select_component->addOption('Seleccione...', '');
        foreach ($this->get_origin_data() as $origin) {
            $select_component->addOption($origin['name'], $origin['id']);
        }
        return $select_component;
    }

    public function get_destiny_select(string $name = 'destiny')
    {
        $select_component = git_select_field(['name' => $name]);
        $select_component->setRequired(true);
        $select_component->attributes->set('target', "selector_route_destiny_$this->id");
        $select_component->addOption('Seleccione...', '');
        foreach ($this->get_destiny_data() as $origin) {
            $class = array_map(fn($destiny) => "show_if_origin_{$destiny}", $origin['origin']);
            $select_component->addOption(
                $origin['name'],
                $origin['id'],
                false,
                ['class' => $class]
            );
        }
        return $select_component;
    }

    public function get_time_select(string $name = 'time')
    {
        $select_component = git_select_field(['name' => $name]);
        $select_component->setRequired(true);
        $select_component->attributes->set('target', "selector_route_time_$this->id");
        $select_component->addOption('Seleccione...', '');
        foreach ($this->get_time_data() as $time) {
            $class = array_map(fn($route) => "show_if_origin_{$route['origin']}_destiny_{$route['destiny']}", $time['routes']);
            $select_component->addOption(
                git_time_create($time['time'])->pretty(),
                $time['time'],
                false,
                ['class' => $class]
            );
        }
        return $select_component;
    }


    private function get_origin_data()
    {
        $origins = [];
        $processed_location_ids = [];
        foreach ($this->get_routes() as $route) {
            if (!isset($processed_location_ids[$route->getOrigin()->id])) {
                $origins[] = [
                    'id' => $route->getOrigin()->id,
                    'name' => $route->getOrigin()->name,
                ];
                $processed_location_ids[$route->getOrigin()->id] = true;
            }
        }
        return $origins;
    }

    private function get_destiny_data()
    {
        $origins = [];
        $processed_location_ids = [];
        foreach ($this->get_routes() as $route) {
            $destiny_id = $route->getDestiny()->id;
            $origin_id = $route->getOrigin()->id;

            if (!isset($processed_location_ids[$destiny_id])) {
                $origins[] = [
                    'id' => $destiny_id,
                    'name' => $route->getDestiny()->name,
                    'origin' => [$origin_id],
                ];
                $processed_location_ids[$destiny_id] = count($origins) - 1;
            } else {
                $index = $processed_location_ids[$destiny_id];
                $origins[$index]['origin'][] = $origin_id;
            }
        }
        return $origins;
    }

    private function get_time_data()
    {
        $data = [];
        foreach ($this->get_routes() as $route) {
            $add = true;
            for ($i = 0; $i < sizeof($data); $i++) {
                if ($data[$i]['time'] === $route->getDepartureTime()->format()) {
                    $data[$i]['routes'][] = [
                        'origin' => $route->getOrigin()->id,
                        'destiny' => $route->getDestiny()->id,
                    ];
                    $add = false;
                }
            }
            if ($add) {
                $data[] = [
                    'time' => $route->getDepartureTime()->format(),
                    'routes' => [
                        [
                            'origin' => $route->getOrigin()->id,
                            'destiny' => $route->getDestiny()->id,
                        ],
                    ]
                ];
            }
        }
        return $data;
    }

    private function get_transport_data()
    {
        $data = [];
        foreach ($this->get_transports() as $transport) {
            $data[] = [
                'id' => $transport->id,
                'nicename' => $transport->nicename,
                'routes' => array_map(fn(Route $route) => [
                    'origin' => $route->getOrigin()->id,
                    'destiny' => $route->getDestiny()->id,
                    'time' => $route->getDepartureTime()->format(),
                ], $transport->getRoutes()),
            ];
        }
        return $data;
    }

    private function get_transports()
    {
        if (!empty($this->transports)) {
            return $this->transports;
        }
        if (git_current_user_has_role(UserRole::OPERATOR)) {
            $this->transports = git_transports([
                'id_operator' => get_current_user_id(),
                'order_by' => 'nicename',
            ]);
        } else {
            $this->transports = git_transports([
                'order_by' => 'nicename',
            ]);
        }
        return $this->transports;
    }

    private function get_routes()
    {
        if (!empty($this->routes)) {
            return $this->routes;
        }

        $this->routes = [];
        $processed_route_ids = [];

        foreach ($this->get_transports() as $transport) {
            foreach ($transport->getRoutes() as $route) {
                if (!isset($processed_route_ids[$route->id])) {
                    $this->routes[] = $route;
                    $processed_route_ids[$route->id] = true;
                }
            }
        }
        return $this->routes;
    }
}
