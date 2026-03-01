<?php
namespace CentralBooking\Data;

use CentralBooking\Data\Location;
use CentralBooking\Data\MetaManager;
use CentralBooking\Data\Route;
use CentralBooking\Data\Service;
use CentralBooking\Data\Transport;
use CentralBooking\Data\Zone;
use WC_Product_Operator;

final class ExportData
{
    public function export(array $data)
    {
        $settings = $data['settings'] ?? false;
        $entities = $data['entities'] ?? false;
        $products = $data['products'] ?? false;

        return [
            'zones' => $entities ? $this->export_zones() : [],
            'routes' => $entities ? $this->export_routes() : [],
            'settings' => $settings ? $this->export_settings() : [],
            'services' => $entities ? $this->export_services() : [],
            'products' => $products ? $this->export_products() : [],
            'locations' => $entities ? $this->export_locations() : [],
            'transports' => $entities ? $this->export_transports() : [],
        ];
    }

    private function export_settings()
    {
        return MetaManager::getMetadata(
            MetaManager::SETTING,
            0,
        );
    }

    private function export_transports()
    {
        return array_map(fn(Transport $transport) => [
            'id' => $transport->id,
            'type' => $transport->type,
            'code' => $transport->code,
            'nicename' => $transport->nicename,
            'metadata' => MetaManager::getMetadata(MetaManager::TRANSPORT, $transport->id),
        ], git_transports());
    }

    private function export_locations()
    {
        return array_map(fn(Location $location) => [
            'id' => $location->id,
            'name' => $location->name,
            'zone_id' => $location->getZone()->id,
            'meta' => MetaManager::getMetadata(MetaManager::LOCATION, $location->id),
        ], git_locations());
    }

    private function export_zones()
    {
        return array_map(fn(Zone $zone) => [
            'id' => $zone->id,
            'name' => $zone->name,
            'meta' => MetaManager::getMetadata(MetaManager::ZONE, $zone->id),
        ], git_zones());
    }

    private function export_services()
    {
        return array_map(fn(Service $service) => [
            'id' => $service->id,
            'icon' => $service->icon,
            'name' => $service->name,
            'price' => $service->price,
            'meta' => MetaManager::getMetadata(MetaManager::SERVICE, $service->id),
        ], git_services());
    }

    private function export_routes()
    {
        return array_map(fn(Route $route) => [
            'id' => $route->id,
            'type' => $route->type,
            'origin_id' => $route->getOrigin()->id,
            'destiny_id' => $route->getDestiny()->id,
            'arrival_time' => $route->getArrivalTime()->format(),
            'departure_time' => $route->getDepartureTime()->format(),
            'meta' => MetaManager::getMetadata(MetaManager::ROUTE, $route->id),
        ], git_routes());
    }

    private function export_products()
    {
        return array_map(fn(WC_Product_Operator $p) => [
            'id' => $p->get_id(),
            'name' => $p->get_name(),
            'type' => $p->get_type(),
            'type_way' => $p->get_meta('type_way'),
            'type_transport' => $p->get_meta('type_transport'),
            'type_route' => $p->get_meta('type_route'),
            'status' => $p->get_status(),
            'prices' => [
                'kid' => $p->get_price_kid(),
                'rpm' => $p->get_price_rpm(),
                'sale' => $p->get_sale_price(),
                'extra' => $p->get_price_extra(),
                'regular' => $p->get_regular_price(),
                'standard' => $p->get_price_standard(),
                'flexible' => $p->get_price_flexible(),
            ],
            'capacity' => [
                'extra' => $p->get_capacity_extra(),
                'people' => $p->get_capacity_people(),
            ],
            'is_purchasable' => $p->is_purchasable(),
            'zones' => [
                'origin' => $p->get_meta('zone_origin'),
                'destiny' => $p->get_meta('zone_destiny'),
            ],
            'has_switch_route' => $p->get_meta('has_switch_route') === 'yes',
            'has_carousel_transport' => $p->get_meta('has_carousel_transport') === 'yes',
        ], wc_get_products([
                'limit' => -1,
                'type' => 'operator',
                'status' => ['publish', 'pending', 'draft', 'future', 'private', 'trash', 'auto-draft'],
            ]));
    }
}
