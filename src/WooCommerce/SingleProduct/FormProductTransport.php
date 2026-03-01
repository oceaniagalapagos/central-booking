<?php
namespace CentralBooking\WooCommerce\SingleProduct;

use CentralBooking\Data\Constants\PriceExtraConstants;
use CentralBooking\Data\Constants\PassengerConstants;
use CentralBooking\Data\Constants\TypeOperation;
use CentralBooking\Data\Constants\TypeWay;
use CentralBooking\Data\Service;
use CentralBooking\Data\Transport;
use CentralBooking\GUI\ComponentInterface;
use CentralBooking\GUI\CounterComponent;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\WooCommerce\CalculateTicketPrice;
use WC_Product_Operator;

class FormProductTransport implements DisplayerInterface
{
    public function __construct(private WC_Product_Operator $product)
    {
    }

    /**
     * @param array $args
     * @return array<Transport>
     */
    public static function queryTransports(array $args)
    {
        $routes = FormProductRoute::query_routes($args);
        $transports = [];
        foreach ($routes as $route) {
            foreach ($route->getTransports() as $transport) {
                $transport->setRoutes([$route]);
                $transports[$transport->id] = $transport;
            }
        }
        $transports = array_values($transports);
        if ($args['split_alias'] ?? false) {
            foreach ($transports as $transport) {
                foreach ($transport->getAlias() as $alias) {
                    $transport_clone = clone $transport;
                    $transport_clone->nicename = $alias;
                    $transports[] = $transport_clone;
                }
            }
        }
        return self::sortTransports($transports);
    }

    /**
     * Summary of sort_transports
     * @param array<Transport> $transports
     * @return array
     */
    private static function sortTransports(array $transports)
    {
        usort($transports, function (Transport $a, Transport $b) {
            return $a->nicename <=> $b->nicename;
        });
        foreach ($transports as $transport) {
            $services = $transport->getServices();
            usort($services, function (Service $a, Service $b) {
                return $a->name <=> $b->name;
            });
            $transport->setServices($services);
        }
        return $transports;
    }

    public function render()
    {
        $calculator = new CalculateTicketPrice();
        $counter_rpm = new CounterComponent();
        $counter_kid = new CounterComponent();
        $counter_extra = new CounterComponent();
        $counter_standard = new CounterComponent();
        $prices = $calculator->getPrices($this->product);
        $type_way = $this->product->get_type_way() ?? TypeWay::NONE;
        $type_transport = $this->product->get_type_operation() ?? TypeOperation::NONE;
        $maximum_extras = $this->product->get_capacity_extra();
        $maximum_persons = $this->product->get_capacity_people();
        $enable_carousel_transports = $this->product->is_carousel_transport();

        $id_button_next = 'button_next_' . rand();
        $id_button_prev = 'button_prev_' . rand();
        $id_carousel_goes = 'carousel_goes_' . rand();
        $id_carousel_returns = 'carousel_returns_' . rand();
        $id_origin_label_goes = 'origin_label_goes_' . rand();
        $id_destiny_label_goes = 'destiny_label_goes_' . rand();
        $id_total_amount_label = 'total_amount_label_' . rand();
        $id_date_trip_label_goes = 'date_trip_label_goes_' . rand();
        $id_origin_label_returns = 'origin_label_returns_' . rand();
        $id_services_amount_label = 'services_amount_label_' . rand();
        $id_subtotal_amount_label = 'subtotal_amount_label_' . rand();
        $id_destiny_label_returns = 'destiny_label_returns_' . rand();
        $id_date_trip_label_returns = 'date_trip_label_returns_' . rand();
        $id_transports_container_goes = 'transports_container_goes_' . rand();
        $id_transports_container_returns = 'transports_container_returns_' . rand();
        $id_transports_options_container_goes = 'transports_options_container_goes_' . rand();
        $id_transports_options_container_returns = 'transports_options_container_returns_' . rand();

        wp_enqueue_script(
            'pane-form-transport',
            CENTRAL_BOOKING_URL . '/assets/js/client/product/pane-form-transport.js',
            [],
            null
        );

        wp_localize_script(
            'pane-form-route',
            'dataTransport',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'hookFetchTransports' => 'git_fetch_transports',
                'typeTransport' => $type_transport,
                'prices' => $prices,
                'issues' => [
                    PassengerConstants::KID->value => git_get_setting('form_message_kid', ''),
                    PassengerConstants::RPM->value => git_get_setting('form_message_rpm', ''),
                    PassengerConstants::STANDARD->value => git_get_setting('form_message_standard', ''),
                    PriceExtraConstants::EXTRA->value => git_get_setting('form_message_extra', ''),
                    PriceExtraConstants::FLEXIBLE->value => git_get_setting('form_message_flexible', ''),
                    PriceExtraConstants::TERMS_CONDITIONS->value => git_get_setting('form_message_terms_conditions', ''),
                ],
                'maximumExtras' => $maximum_extras,
                'maximumPersons' => $maximum_persons,
                'splitByAlias' => $this->product->get_meta('split_transport_by_alias', true) === 'yes',
                'elements' => [
                    'idButtonNext' => $id_button_next,
                    'idButtonPrev' => $id_button_prev,
                    'idCarouselGoes' => $id_carousel_goes,
                    'idCarouselReturns' => $id_carousel_returns,
                    'idOriginLabelGoes' => $id_origin_label_goes,
                    'idDestinyLabelGoes' => $id_destiny_label_goes,
                    'idDateTripLabelGoes' => $id_date_trip_label_goes,
                    'idOriginLabelReturns' => $id_origin_label_returns,
                    'idDestinyLabelReturns' => $id_destiny_label_returns,
                    'idDateTripLabelReturns' => $id_date_trip_label_returns,
                    'idTransportsContainerGoes' => $id_transports_container_goes,
                    'idTransportsContainerReturns' => $id_transports_container_returns,
                    'idTransportsOptionsContainerGoes' => $id_transports_options_container_goes,
                    'idTransportsOptionsContainerReturns' => $id_transports_options_container_returns,
                    'idTotalAmountLabel' => $id_total_amount_label,
                    'idServicesAmountLabel' => $id_services_amount_label,
                    'idSubtotalAmountLabel' => $id_subtotal_amount_label,
                    'idCounters' => [
                        'idRPM' => $counter_rpm->id,
                        'idKid' => $counter_kid->id,
                        'idExtra' => $counter_extra->id,
                        'idStandard' => $counter_standard->id,
                    ]
                ]
            ]
        );
        ?>
        <div id="git-form-product-transport" style="display: none;">
            <div id="<?= $id_transports_container_goes ?>">
                <div class="third-container">
                    <span class="right fw-medium" id="<?= $id_origin_label_goes ?>"></span>
                    <span class="center fw-medium" id="<?= $id_date_trip_label_goes ?>"></span>
                    <span class="left fw-medium" id="<?= $id_destiny_label_goes ?>"></span>
                </div>
                <?php if ($enable_carousel_transports): ?>
                    <div id="<?= $id_carousel_goes ?>" class="carousel slide my-3">
                        <div class="carousel-indicators" style="display: none;"></div>
                        <div class="carousel-inner"></div>
                    </div>
                <?php endif; ?>
                <div id="<?= $id_transports_options_container_goes ?>" class="option-container"></div>
            </div>
            <?php if ($type_way !== TypeWay::ONE_WAY->slug()): ?>
                <hr>
                <div id="<?= $id_transports_container_returns ?>">
                    <div class="third-container">
                        <span class="right fw-medium" id="<?= $id_origin_label_returns ?>"></span>
                        <span class="center fw-medium" id="<?= $id_date_trip_label_returns ?>"></span>
                        <span class="left fw-medium" id="<?= $id_destiny_label_returns ?>"></span>
                    </div>
                    <?php if ($enable_carousel_transports): ?>
                        <div id="<?= $id_carousel_returns ?>" class="carousel slide my-3">
                            <div class="carousel-indicators" style="display: none;"></div>
                            <div class="carousel-inner"></div>
                        </div>
                    <?php endif; ?>
                    <div id="<?= $id_transports_options_container_returns ?>" class="option-container"></div>
                </div>
            <?php endif; ?>

            <ul class="nav nav-tabs m-0" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="standard-tab" data-bs-toggle="tab" data-bs-target="#standard-tab-pane"
                        type="button" role="tab" aria-controls="standard-tab-pane" aria-selected="true">Regular</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reduce-tab" data-bs-toggle="tab" data-bs-target="#reduce-tab-pane"
                        type="button" role="tab" aria-controls="reduce-tab-pane" aria-selected="false">Preferente</button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div id="standard-tab-pane" class="tab-pane fade show active" role="tabpanel" aria-labelledby="standard-tab"
                    tabindex="0">
                    <?php
                    $this->extra_component(
                        'Ticket — $' . $prices[PassengerConstants::STANDARD->slug()],
                        PassengerConstants::STANDARD->slug(),
                        $counter_standard
                    );
                    $this->extra_component(
                        'Carga Extra — $' . $prices[PriceExtraConstants::EXTRA->value],
                        PriceExtraConstants::EXTRA->value,
                        $counter_extra
                    );
                    ?>
                </div>
                <div id="reduce-tab-pane" class="tab-pane fade" role="tabpanel" aria-labelledby="reduce-tab" tabindex="0">
                    <?php
                    echo git_get_setting('form_message_local', '');
                    $this->extra_component(
                        'Edad Preferente — $' . $prices[PassengerConstants::KID->slug()],
                        PassengerConstants::KID->slug(),
                        $counter_kid
                    );
                    $this->extra_component(
                        'Movilidad Reducida — $' . $prices[PassengerConstants::RPM->slug()],
                        PassengerConstants::RPM->slug(),
                        $counter_rpm
                    );
                    ?>
                </div>
            </div>
            <hr class="m-0">
            <?php
            $this->extra_component(
                'Flexible — $' . $prices[PriceExtraConstants::FLEXIBLE->value],
                PriceExtraConstants::FLEXIBLE->value,
                git_string_to_component($this->checkbox_switch('flexible', true))
            );
            $this->extra_component(
                'He leído los términos y condiciones',
                'terms_conditions',
                git_string_to_component($this->checkbox_switch('terms_conditions'))
            );
            ?>
            <hr class="m-0">
            <div class="d-flex justify-content-between align-items-center p-3">
                <b>Subtotal:</b>
                <b id="<?= $id_subtotal_amount_label ?>" class="text-center" style="width: 100px">$0</b>
            </div>
            <div class="d-flex justify-content-between align-items-center p-3">
                <b>Servicios:</b>
                <b id="<?= $id_services_amount_label ?>" class="text-center" style="width: 100px">$0</b>
            </div>
            <hr class="m-0">
            <div class="d-flex justify-content-between align-items-center p-3">
                <b>Total:</b>
                <b id="<?= $id_total_amount_label ?>" class="text-center" style="width: 100px">$0</b>
            </div>
            <button id="<?= $id_button_prev ?>" type="button" class="btn btn-secondary">Atras</button>
            <button id="<?= $id_button_next ?>" type="button" class="btn btn-primary">Continuar Reserva</button>
        </div>
        <?php
    }

    private function checkbox_switch(string $name, bool $checked = false): string
    {
        ob_start();
        ?>
        <div class="form-check form-switch d-flex justify-content-center" style="width: 100px">
            <input class="form-check-input" type="checkbox" role="switch" name="<?= $name ?>" <?= $checked ? 'checked' : '' ?>>
        </div>
        <?php
        return ob_get_clean();
    }

    private function extra_component(string $label, string $target, ComponentInterface $compnent)
    {
        ?>
        <div class="d-flex justify-content-between align-items-center p-3">
            <div class="h-100">
                <span class="align-middle"><?= $label ?></span>
                <i class="bi bi-question-circle control-issue" data-target="<?= $target ?>" style="cursor: pointer;"></i>
            </div>
            <?= $compnent->compact(); ?>
        </div>
        <?php
    }
}
