<?php
namespace CentralBooking\WooCommerce\SingleProduct;

use CentralBooking\Data\Constants\TypeWay;
use CentralBooking\Data\Route;
use CentralBooking\Data\Services\RouteService;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\InputFloatingLabelComponent;
use WC_Product_Operator;

class FormProductRoute implements DisplayerInterface
{
    public function __construct(private WC_Product_Operator $product)
    {
    }

    /**
     * @param array $args
     * @return array<Route>
     */
    public static function query_routes(array $args)
    {
        $zoneOrigin = git_zone_by_name($args['name_zone_origin'] ?? '');
        $zoneDestiny = git_zone_by_name($args['name_zone_destiny'] ?? '');
        if (!$zoneOrigin || !$zoneDestiny) {
            return [];
        }

        $service = new RouteService();
        $routes = [];

        foreach ($zoneOrigin->getLocations() as $locationOrigin) {
            foreach ($zoneDestiny->getLocations() as $locationDestiny) {
                $filter = [
                    'id_origin' => $locationOrigin->id,
                    'id_destiny' => $locationDestiny->id,
                ];
                $routes = array_merge($routes, $service->find($filter)->getItems());
            }
        }

        if (!isset($args['schedule'])) {
            return $routes;
        }

        $results = [];

        foreach ($routes as $route) {
            $hour = intval(substr($route->getDepartureTime()->format('H:i:s'), 0, 2));
            if (($args['schedule'] === 'morning' && $hour >= 0 && $hour < 12)) {
                $results[] = $route;
            } else if (($args['schedule'] === 'afternoon' && $hour >= 12 && $hour < 24)) {
                $results[] = $route;
            }
        }
        return $results;
    }

    public function render()
    {
        $origin = '';
        $destiny = '';
        $type_way = $this->product->get_type_way() ?? TypeWay::ONE_WAY;
        $is_switchable = $this->product->is_switchable();

        $date_trip_goes = git_date_trip_field('date_trip_goes');
        $date_trip_returns = git_date_trip_field('date_trip_returns');
        $schedule_goes = git_select_field(['name' => 'schedule_goes']);
        $schedule_returns = git_select_field(['name' => 'schedule_returns']);

        $id_radio_group = 'radio_group_' . rand();
        $id_button_next = 'button_next_' . rand();
        $id_switch_button = 'switch_button_' . rand();
        $id_radio_one_way = 'radio_one_way_' . rand();
        $id_origin_label = 'location_label_' . rand();
        $id_destiny_label = 'location_label_' . rand();
        $id_radio_double_way = 'radio_double_way_' . rand();
        $id_container_trip_goes = 'container_trip_goes_' . rand();
        $id_container_trip_returns = 'container_trip_returns_' . rand();

        $origin = $this->product->get_zone_origin()->name;
        $destiny = $this->product->get_zone_destiny()->name;

        foreach ([$schedule_goes, $schedule_returns] as $schedule_select) {
            $schedule_select->addOption('Mañana', 'morning');
            $schedule_select->addOption('Tarde', 'afternoon');
        }

        wp_enqueue_script(
            'pane-form-route',
            CENTRAL_BOOKING_URL . '/assets/js/client/product/pane-form-route.js',
            [],
            null
        );

        wp_localize_script(
            'pane-form-route',
            'dataRoute',
            [
                'origin' => $origin,
                'destiny' => $destiny,
                'typeWay' => $type_way === TypeWay::DOUBLE_WAY ? TypeWay::DOUBLE_WAY->slug() : TypeWay::ONE_WAY->slug(),
                'elements' => [
                    'idButtonNext' => $id_button_next,
                    'idOriginLabel' => $id_origin_label,
                    'idRadioOneWay' => $id_radio_one_way,
                    'idSwitchButton' => $id_switch_button,
                    'idDestinyLabel' => $id_destiny_label,
                    'idDateTripGoes' => $date_trip_goes->id,
                    'idScheduleGoes' => $schedule_goes->id,
                    'idRadioDoubleWay' => $id_radio_double_way,
                    'idDateTripReturns' => $date_trip_returns->id,
                    'idScheduleReturns' => $schedule_returns->id,
                    'idContainerTripGoes' => $id_container_trip_goes,
                    'idContainerTripReturns' => $id_container_trip_returns,
                ],
            ]
        );
        ?>
        <div id="git-form-product-route">
            <div id="<?= $id_radio_group ?>">
                <?php if ($type_way === TypeWay::ONE_WAY): ?>
                    <input id="<?= $id_radio_one_way ?>" class="form-control btn-check" name="type_way" type="radio"
                        value="<?= TypeWay::ONE_WAY->slug() ?>" checked>
                    <label class="btn btn-outline-primary m-1"
                        for="<?= $id_radio_one_way ?>"><?= TypeWay::ONE_WAY->label() ?></label>
                <?php elseif ($type_way === TypeWay::DOUBLE_WAY): ?>
                    <input id="<?= $id_radio_double_way ?>" class="form-control btn-check" name="type_way" type="radio"
                        value="<?= TypeWay::DOUBLE_WAY->slug() ?>" checked>
                    <label class="btn btn-outline-primary m-1" for="<?= $id_radio_double_way ?>"
                        checked><?= TypeWay::DOUBLE_WAY->label() ?></label>
                <?php elseif ($type_way === TypeWay::ANY_WAY): ?>
                    <input id="<?= $id_radio_one_way ?>" class="form-control btn-check" name="type_way" type="radio"
                        value="<?= TypeWay::ONE_WAY->slug() ?>" checked>
                    <label class="btn btn-outline-primary m-1"
                        for="<?= $id_radio_one_way ?>"><?= TypeWay::ONE_WAY->label() ?></label>
                    <input id="<?= $id_radio_double_way ?>" class="form-control btn-check" name="type_way" type="radio"
                        value="<?= TypeWay::DOUBLE_WAY->slug() ?>">
                    <label class="btn btn-outline-primary m-1"
                        for="<?= $id_radio_double_way ?>"><?= TypeWay::DOUBLE_WAY->label() ?></label>
                <?php endif; ?>
            </div>
            <div class="my-3" style="display: flex">
                <div class="w-50 text-start">
                    <span id="<?= $id_origin_label ?>" class="fs-5 px-5"><?= $origin ?></span>
                </div>
                <?php if ($is_switchable): ?>
                    <button id="<?= $id_switch_button ?>" class="btn btn-outline-primary" type="button" data-bs-toggle="tooltip"
                        data-bs-placement="top" data-bs-title="Cambiar ruta" style="cursor: pointer;">
                        <i class="bi bi-arrow-left-right"></i>
                    </button>
                <?php endif; ?>
                <div class="w-50 text-end">
                    <span id="<?= $id_destiny_label ?>" class="fs-5 px-5"><?= $destiny ?></span>
                </div>
            </div>
            <div id="<?= $id_container_trip_goes ?>" class="p-2">
                <p class="fs-5">Fecha y hora de ida</p>
                <?php
                $floating_date_trip_goes = new InputFloatingLabelComponent($date_trip_goes, 'Fecha');
                $floating_schedule_goes = new InputFloatingLabelComponent($schedule_goes, 'Hora');
                $floating_date_trip_goes->render();
                $floating_schedule_goes->render();
                ?>
            </div>
            <?php if ($type_way !== TypeWay::ONE_WAY): ?>
                <div id="<?= $id_container_trip_returns ?>" class="p-2"
                    style="display: <?= $type_way === TypeWay::ANY_WAY ? 'none' : '' ?>;">
                    <p class="fs-5">Fecha y hora de vuelta</p>
                    <?php
                    $floating_date_trip_returns = new InputFloatingLabelComponent($date_trip_returns, 'Fecha');
                    $floating_schedule_returns = new InputFloatingLabelComponent($schedule_returns, 'Hora');
                    $floating_date_trip_returns->render();
                    $floating_schedule_returns->render();
                    ?>
                </div>
            <?php endif; ?>
            <button id="<?= $id_button_next ?>" class="btn btn-primary" type="button">Continuar Reserva</button>
        </div>
        <?php
    }
}
