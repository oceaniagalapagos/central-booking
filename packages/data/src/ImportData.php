<?php
namespace CentralBooking\Data;

use WC_Product_Operator;

final class ImportData
{
    private array $zones_ids_map = [];
    private array $routes_ids_map = [];
    private array $services_ids_map = [];
    private array $locations_ids_map = [];
    private array $operators_ids_map = [];
    private array $transports_ids_map = [];

    public function import(array $data)
    {
        $zones = $data['zones'] ?? [];
        $routes = $data['routes'] ?? [];
        $settings = $data['settings'] ?? [];
        $products = $data['products'] ?? [];
        $services = $data['services'] ?? [];
        $locations = $data['locations'] ?? [];
        $transports = $data['transports'] ?? [];

        $this->import_settings($settings);
        $this->import_zones($zones);
        $this->import_locations($locations);
        $this->import_routes($routes);
        $this->import_services($services);
        $this->import_transports($transports);
        $this->import_products($products);
    }

    private function import_settings(array $settings)
    {
        foreach ($settings as $key => $value) {
            git_set_setting($key, $value);
        }
    }

    private function import_zones(array $zones)
    {
        foreach ($zones as $zone) {
            $id_prev = $zone['id'];
            unset($zone['id']);

            $zone_obj = git_zone_create($zone);
            $zone_saved = $zone_obj->save();

            if ($zone_saved) {
                $this->zones_ids_map[$id_prev] = $zone_obj->id;
            }
        }
    }

    private function import_locations(array $locations)
    {
        foreach ($locations as $location) {
            $id_prev = $location['id'];
            unset($zone['id']);

            $location['zone_id'] = $this->zones_ids_map[$location['zone_id']];

            $location_obj = git_location_create($location);
            $location_saved = $location_obj->save();

            if ($location_saved) {
                $this->locations_ids_map[$id_prev] = $location_obj->id;
            }
        }
    }

    private function import_routes(array $routes)
    {
        foreach ($routes as $route) {
            $id_prev = $route['id'];
            unset($zone['id']);

            $route['origin_id'] = $this->zones_ids_map[$route['origin_id']];
            $route['destiny_id'] = $this->zones_ids_map[$route['destiny_id']];

            $route_obj = git_route_create($route);
            $route_saved = $route_obj->save();

            if ($route_saved) {
                $this->routes_ids_map[$id_prev] = $route_obj->id;
            }
        }
    }

    private function import_services(array $services)
    {
        foreach ($services as $service) {
            $id_prev = $service['id'];
            unset($zone['id']);

            $service_obj = git_service_create($service);
            $service_saved = $service_obj->save();

            if ($service_saved) {
                $this->services_ids_map[$id_prev] = $service_obj->id;
            }
        }
    }

    private function import_transports(array $transports)
    {
        foreach ($transports as $transport) {
            $id_prev = $transport['id'];
            unset($zone['id']);

            $transport_obj = git_transport_create($transport);
            $transport_saved = $transport_obj->save();

            if ($transport_saved) {
                $this->transports_ids_map[$id_prev] = $transport_obj->id;
            }
        }
    }

    private function import_products(array $products)
    {
        foreach ($products as $product) {
            $product_obj = new WC_Product_Operator(0);
            $product_obj->set_name($product['name'] ?? '');

            $product_obj->set_price_kid($product['prices']['kid'] ?? 0);
            $product_obj->set_price_rpm($product['prices']['rpm'] ?? 0);
            $product_obj->set_sale_price($product['prices']['sale'] ?? 0);
            $product_obj->set_price_extra($product['prices']['extra'] ?? 0);
            $product_obj->set_price_regular($product['prices']['regular'] ?? 0);
            $product_obj->set_price_standard($product['prices']['standard'] ?? 0);
            $product_obj->set_price_flexible($product['prices']['flexible'] ?? 0);
            $product_obj->set_capacity_extra($product['capacity']['extra'] ?? 0);
            $product_obj->set_capacity_people($product['capacity']['people'] ?? 0);
            $product_obj->set_purchasable($product['is_purchasable']);

            $product_obj->set_meta_data([
                'zone_origin' => $product['zones']['origin'] ?? 0,
                'zone_destiny' => $product['zones']['destiny'] ?? 0,
                'has_switch_route' => $product['has_switch_route'] ?? 0,
                'has_carousel_transport' => $product['has_carousel_transport'] ?? 0,
            ]);

            $product_obj->save();
        }
    }
}
