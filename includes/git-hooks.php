<?php

use CentralBooking\Admin\AdminRouter;
use CentralBooking\Admin\Form\FormCoupon;
use CentralBooking\Admin\Form\FormLocation;
use CentralBooking\Admin\Form\FormOperator;
use CentralBooking\Admin\Form\FormRoute;
use CentralBooking\Admin\Form\FormService;
use CentralBooking\Admin\Form\FormTicket;
use CentralBooking\Admin\Form\FormTransfer;
use CentralBooking\Admin\Form\FormTransport;
use CentralBooking\Admin\Form\FormWebhook;
use CentralBooking\Admin\Form\FormZone;
use CentralBooking\Admin\Setting\SettingsBooking;
use CentralBooking\Admin\Setting\SettingsKeys;
use CentralBooking\Admin\Setting\SettingsLabels;
use CentralBooking\Admin\Setting\SettingsNotifications;
use CentralBooking\Admin\Setting\SettingsViewer;
use CentralBooking\Admin\Setting\SettingsWebhooks;
use CentralBooking\Admin\View\TableOperators;
use CentralBooking\Admin\View\TableCoupons;
use CentralBooking\Admin\View\TableLocations;
use CentralBooking\Admin\View\TablePassengers;
use CentralBooking\Admin\View\TableRoutes;
use CentralBooking\Admin\View\TableServices;
use CentralBooking\Admin\View\TableTickets;
use CentralBooking\Admin\View\TableTransports;
use CentralBooking\Admin\View\TableZones;
use CentralBooking\Admin\Setting\SettingsGeneral;
use CentralBooking\Data\Constants\LogLevel;
use CentralBooking\Data\Constants\LogSource;
use CentralBooking\Data\Constants\PassengerConstants;
use CentralBooking\Data\Transport;
use CentralBooking\Data\Services\ErrorService;
use CentralBooking\Data\Constants\TicketStatus;
use CentralBooking\Data\Constants\UserRole;
use CentralBooking\Data\Repository\Migration;
use CentralBooking\Implementation\Document\DocumentSallingRequest;
use CentralBooking\Implementation\Document\DocumentTrip;
use CentralBooking\Implementation\Temp\MessageTemporal;
use CentralBooking\PDF\DocumentPdf;
use CentralBooking\Placeholders\PlaceholderEngineCheckout;
use CentralBooking\Profile\Forms\FormEditCoupon;
use CentralBooking\Profile\ProfileDashboard;
use CentralBooking\QR\ErrorCorrectionCode;
use CentralBooking\Implementation\Temp\MessageLevel;
use CentralBooking\Utils\ArrayParser\TransportArray;
use CentralBooking\Utils\Actions\DownloadInvoiceInfo;
use CentralBooking\WooCommerce\CreateOrderLineItem;
use CentralBooking\WooCommerce\ProductForm;
use CentralBooking\WooCommerce\ProductItemCart;
use CentralBooking\WooCommerce\ProductSinglePresentation;
use CentralBooking\WooCommerce\Thankyou;
use CentralBooking\WooCommerce\SingleProduct\FormProduct;
use CentralBooking\WooCommerce\SingleProduct\FormProductNotAvailable;
use CentralBooking\WooCommerce\SingleProduct\FormProductTransport;
use CentralBooking\WooCommerce\ValidateCoupon;
use CentralBooking\Profile\Tables\TableTrip;

defined('ABSPATH') || exit;

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    $url = AdminRouter::get_url_for_class(SettingsGeneral::class);
    $links[] = '<a href="' . $url . '">Ajustes</a>';
    return $links;
}, 1);

add_action('woocommerce_single_product_summary', function () {
    global $product;
    if ($product instanceof WC_Product_Operator) {
        if ($product->is_purchasable()) {
            (new FormProduct($product))->render();
        } else {
            (new FormProductNotAvailable)->render();
        }
    }
}, 25);

add_filter('woocommerce_get_item_data', function ($item_data, $cart_item) {
    $product_item = new ProductItemCart();
    $item_data = array_merge($item_data, $product_item->itemCart($cart_item));
    return $item_data;
}, 10, 2);

add_action('woocommerce_before_calculate_totals', function ($cart_object) {
    foreach ($cart_object->get_cart() as $cart_item) {
        $product = wc_get_product($cart_item['product_id']);
        if ($product->get_type() !== 'operator') {
            continue;
        }
        $cart_item['data']->set_price($cart_item['cart_ticket']->calculatePrice());
    }
});

function git_ajax_validate_coupon(bool $valid, WC_Coupon $coupon)
{
    $validator = new ValidateCoupon();
    return $validator->isValid($coupon) && $valid;
}

add_filter('woocommerce_coupon_is_valid', 'git_ajax_validate_coupon', 10, 2);

add_filter('woocommerce_thankyou_order_received_text', function ($thank_you_text, WC_Order $order) {
    $message = git_get_setting('message_checkout', '');
    $engine = new PlaceholderEngineCheckout($order);
    $processed_message = $engine->process($message);
    $thank_you_text = $processed_message;
    return $thank_you_text;
}, 10, 2);

add_action('woocommerce_checkout_create_order_line_item', function ($item, $cart_item_key, $values, $order) {
    $create_order_line_item = new CreateOrderLineItem();
    $create_order_line_item->add_line_item($item, $values);
}, 10, 4);

add_action('woocommerce_thankyou', function ($order_id) {
    $thankyou = new Thankyou();
    $thankyou->thankyou($order_id);
}, 10, 1);

add_filter('woocommerce_product_data_tabs', function ($tabs) {
    return array_merge($tabs, ProductForm::get_tabs());
});

add_action('woocommerce_product_data_panels', function () {
    ProductForm::get_general_panel();
    ProductForm::get_pricing_panel();
    ProductForm::get_inventory_panel();
});

add_action('woocommerce_process_product_meta_operator', function ($post_id) {
    ProductForm::process_form($post_id);
});

function git_ajax_product_submit()
{
    $nonce = $_POST['_gitnonce'] ?? '';
    if (git_verify_nonce($nonce) === false) {
        return;
    }
    $_POST['flexible'] = isset($_POST['flexible']);
    $_POST['round_trip'] = isset($_POST['round_trip']);
    $_POST['terms_conditions'] = isset($_POST['terms_conditions']);

    $flexible = $_POST['flexible'];
    $round_trip = $_POST['round_trip'];
    $productSinglePresentation = new ProductSinglePresentation();
    $passengers = $_POST['passengers'] ?? [];

    foreach ($passengers as &$passenger) {
        $passenger['type'] = PassengerConstants::STANDARD->slug();
    }

    $add1 = true;
    $add2 = true;

    $add1 = $productSinglePresentation->addToCart(
        (int) $_POST['goes']['id_transport'],
        (int) $_POST['goes']['id_route'],
        $_POST['goes']['date_trip'],
        (int) $_POST['product'],
        $flexible,
        $passengers,
        $_POST['pax'],
    );

    if ($round_trip) {
        $add2 = $productSinglePresentation->addToCart(
            (int) $_POST['returns']['id_transport'],
            (int) $_POST['returns']['id_route'],
            $_POST['returns']['date_trip'],
            (int) $_POST['product'],
            $flexible,
            $passengers,
            $_POST['pax'],
        );
    }

    if ($add1 && $add2) {
        wp_safe_redirect(wc_get_cart_url());
        exit;
    }
    exit;
}

function git_ajax_fetch_tranports()
{
    $response = new TransportArray();
    $_POST['split_alias'] = isset($_POST['split_alias']) && $_POST['split_alias'] === '1' ? true : false;
    wp_send_json_success(array_map(
        fn(Transport $transport) => $response->get_array($transport),
        FormProductTransport::queryTransports($_POST)
    ));
}

add_action('wp_ajax_git_product_submit', 'git_ajax_product_submit');
add_action('wp_ajax_git_fetch_transports', 'git_ajax_fetch_tranports');
add_action('wp_ajax_nopriv_git_product_submit', 'git_ajax_product_submit');
add_action('wp_ajax_nopriv_git_fetch_transports', 'git_ajax_fetch_tranports');

function git_ajax_edit_ticket_status()
{
    $redirect = $_POST['_wp_http_referer'] ?? wp_get_referer();
    $nonce = $_POST['nonce'] ?? '';

    if (wp_verify_nonce($nonce, FormEditCoupon::ACTION_NONCE) === false) {
        FormEditCoupon::writeMessage('El identificador del formulario no es válido o ha expirado.', MessageLevel::WARNING);
        wp_safe_redirect($redirect);
        exit;
    }

    $is_operator = git_current_user_has_role(UserRole::OPERATOR);
    $is_administrator = git_current_user_has_role(UserRole::ADMINISTRATOR);

    if ($is_administrator === false && $is_operator === false) {
        FormEditCoupon::writeMessage('No cuentas con los permisos necesarios necesarios para realizar esta acción.', MessageLevel::WARNING);
        wp_safe_redirect($redirect);
        exit;
    }

    $ticket = git_ticket_by_id((int) ($_POST['id']) ?? 0);

    if ($ticket === null) {
        FormEditCoupon::writeMessage('El ticket no existe.', MessageLevel::ERROR);
        wp_safe_redirect($redirect);
        exit;
    }

    if ($is_operator && ($ticket->status !== TicketStatus::PENDING)) {
        $pending_label = TicketStatus::PENDING->label();
        FormEditCoupon::writeMessage("Como operador, no puedes asignar un estado que no sea {$pending_label}.", MessageLevel::ERROR);
        wp_safe_redirect($redirect);
        exit;
    }

    $status = TicketStatus::fromSlug($_POST['status'] ?? '');

    if ($status === null) {
        FormEditCoupon::writeMessage('El estado asignado al ticket no es válido.', MessageLevel::ERROR);
        wp_safe_redirect($redirect);
        exit;
    }

    $file = $_FILES['proof'] ?? null;
    $proof = $ticket->getProofPayment();

    if ($file === null && $proof === null) {
        FormEditCoupon::writeMessage('No ha subido el documento solicitado.', MessageLevel::ERROR);
        wp_safe_redirect($redirect);
        exit;
    }

    if ($proof === null) {
        $proof = git_proof_payment_create([
            'filename' => $file['name'] ?? '',
        ]);
    }

    $proof->code = $_POST['code'] ?? '';
    $status_previous = $ticket->status;
    $ticket->status = $status;

    if (($file === null) === false) {
        $proof->filename = $file['name'] ?? '';
        $proof->amount = $status === TicketStatus::PAYMENT ? $ticket->total_amount : (int) ($_POST['amount'] ?? 0);
        $file_saved = $proof->saveFile($file);
        if ($file_saved === false) {
            FormEditCoupon::writeMessage('Error al guardar el archivo subido.', MessageLevel::ERROR);
            wp_safe_redirect($redirect);
            exit;
        }
    }

    if ($status === TicketStatus::PAYMENT) {
        $proof->amount = $ticket->total_amount;
        foreach ($ticket->getPassengers() as $passenger) {
            $passenger->approved = true;
        }
    } elseif ($status === TicketStatus::CANCEL) {
        $proof = null;
        foreach ($ticket->getPassengers() as $passenger) {
            $passenger->approved = false;
        }
    } elseif ($status === TicketStatus::PENDING) {
        $proof = null;
        foreach ($ticket->getPassengers() as $passenger) {
            $passenger->approved = false;
        }
    } elseif ($status === TicketStatus::PARTIAL) {
        $proof->amount = 100 * (int) ($_POST['amount'] ?? 0);
        $approved_passengers = array_map('intval', $_POST['passengers'] ?? []);
        foreach ($ticket->getPassengers() as $passenger) {
            $passenger->approved = in_array(
                $passenger->id,
                $approved_passengers,
                true
            );
        }
    }

    $ticket->setProofPayment($proof);
    $ticket_saved = $ticket->save();

    if ($ticket_saved === true) {
        FormEditCoupon::writeMessage('Se ha actualizado el ticket exitosamente.', MessageLevel::SUCCESS);
    } else {
        FormEditCoupon::writeMessage('Error al guardar el ticket.', MessageLevel::ERROR);
    }

    git_log_create(
        source: LogSource::TICKET,
        id_source: $ticket->id,
        message: "El estado del ticket ha sido cambiado de {$status_previous->label()} a {$ticket->status->label()} por el usuario <code>" . wp_get_current_user()->user_login . "</code>",
        level: LogLevel::INFO,
    );
    wp_safe_redirect($redirect);
    exit;
}

add_action('wp_ajax_git_transfer_passengers', function () {
    $referer = $_POST['_wp_http_referer'] ?? wp_get_referer();
    $nonce = $_POST['nonce'] ?? '';

    if (wp_verify_nonce($nonce, FormRoute::NONCE_ACTION) === false) {

        FormTransfer::writeMessage('El identificador del formulario no es válido o ha expirado.', MessageLevel::WARNING);

    } elseif (git_current_user_has_role(UserRole::ADMINISTRATOR) === false) {

        FormTransfer::writeMessage('No tiene permisos para realizar esta acción.', MessageLevel::ERROR);

    } else {

        $routes = git_routes($_POST);
        $transport = git_transport_by_id((int) ($_POST['transport'] ?? 0));

        if ($routes === [] || $transport === null) {

            FormTransfer::writeMessage('No se ha encontrado ruta o transporte con los parámetros especificados en la base de datos.', MessageLevel::ERROR);

        } else {

            $route = $routes[0];
            $passengers = $_POST['passengers'] ?? [];

            $is_available = $transport->checkAvaility(
                $route,
                git_date_create($_POST['date_trip'] ?? 'today'),
                count($passengers)
            );

            if ($is_available === false) {

                FormTransfer::writeMessage('No se ha podido realizar el traslado.', MessageLevel::ERROR);

            } else {

                foreach ($passengers as $passenger_id) {
                    $passenger = git_passenger_by_id((int) $passenger_id);
                    if ($passenger === null) {
                        continue;
                    }
                    $transfered = $passenger->transfer(
                        $transport,
                        $route,
                        git_date_create($_POST['date_trip'] ?? '')
                    );
                    if ($transfered === true) {
                        $passenger->save();
                        $ticket = $passenger->getTicket();
                        $ticket->flexible = false;
                        $ticket->save();
                    }
                }

                $referer = AdminRouter::get_url_for_class(TablePassengers::class);
            }
        }
    }

    wp_safe_redirect($referer);
    exit;
});

function git_ajax_toggle_flexible()
{
    $redirect = $_POST['_wp_http_referer'] ?? wp_get_referer();
    $nonce = $_POST['_wpnonce'] ?? '';

    if (wp_verify_nonce($nonce, TableTickets::NONCE_ACTION) === false) {

        TableTickets::writeMessage('El identificador del formulario no es válido o ha expirado.', MessageLevel::ERROR);

    } elseif (git_current_user_has_role(UserRole::ADMINISTRATOR) === false) {

        TableTickets::writeMessage('No tiene permisos para realizar esta acción.', MessageLevel::ERROR);

    }

    $force = null;

    if (isset($_POST['flexible'])) {
        $force = (bool) $_POST['flexible'];
    }

    $ticket = git_ticket_by_id((int) $_POST['id'] ?? -1);

    if ($ticket === null) {

        TableTickets::writeMessage('El ticket no existe en la base de datos.', MessageLevel::ERROR);

    } else {

        $ticket->toggleFlexible($force);
        $saved = $ticket->save();

        if ($saved) {

            TableTickets::writeMessage('Flexibilidad del ticket actualizada correctamente.', MessageLevel::SUCCESS);

        } else {

            TableTickets::writeMessage('Error al actualizar la flexibilidad del ticket.', MessageLevel::ERROR);

        }
    }

    wp_safe_redirect($redirect);
    exit;
}

add_action('wp_ajax_git_edit_toggle_flexible', 'git_ajax_toggle_flexible');

function git_ajax_edit_transport()
{
    $redirect = $_POST['_wp_http_referer'] ?? wp_get_referer();
    $nonce = $_POST['_wpnonce'] ?? '';

    if (wp_verify_nonce($nonce, FormTransport::NONCE_ACTION) === false) {
        FormTransport::writeMessage('El identificador del formulario no es válido o ha expirado.', MessageLevel::ERROR);
        wp_safe_redirect($redirect);
        exit;
    }

    if (git_current_user_has_role(UserRole::ADMINISTRATOR) === false) {
        FormTransport::writeMessage('No tiene permisos para realizar esta acción.', MessageLevel::ERROR);
        wp_safe_redirect($redirect);
        exit;
    }

    $transport = git_transport_create($_POST);
    $saved = $transport->save();

    if ($saved) {
        $redirect = AdminRouter::get_url_for_class(TableTransports::class);
        TableTransports::writeMessage('Transporte guardado correctamente.');
    } else {
        FormTransport::writeMessage('Error al guardar el transporte.', MessageLevel::ERROR);
    }

    wp_safe_redirect($redirect);
    exit;
}

function git_ajax_edit_location()
{
    $nonce = $_POST['git_nonce'] ?? '';
    $redirect = $_POST['git_referer'] ?? wp_get_referer();

    if (git_verify_nonce($nonce) === false) {
        FormLocation::writeMessage('El identificador del formulario no es válido o ha expirado.', MessageLevel::WARNING);
        wp_safe_redirect($redirect);
        exit;
    }

    if (git_current_user_has_role(UserRole::ADMINISTRATOR) === false) {
        FormLocation::writeMessage('No tiene permisos para realizar esta acción.', MessageLevel::WARNING);
        wp_safe_redirect($redirect);
        exit;
    }

    $location = git_location_create($_POST);
    $saved = $location->save();

    if ($saved) {
        TableLocations::writeMessage('Se ha creado una nueva ubicación exitosamente.', MessageLevel::SUCCESS);
        $redirect = AdminRouter::get_url_for_class(TableLocations::class);
    } else {
        FormLocation::writeMessage('Ha ocurrido un error a la hora de crear una nueva ubicación.', MessageLevel::ERROR);
    }

    wp_safe_redirect($redirect);
    exit;
}

function git_ajax_edit_operator()
{
    $nonce = $_POST['git_nonce'] ?? '';
    $referer = $_POST['git_referer'] ?? wp_get_referer();
    if (git_verify_nonce($nonce) === false) {
        FormOperator::writeMessage('El identificador del formulario no es válido o ha expirado.', MessageLevel::WARNING);
        wp_safe_redirect($referer);
        exit;
    }

    if (git_current_user_has_role(UserRole::ADMINISTRATOR) === false) {
        FormOperator::writeMessage('No tiene permisos para realizar esta acción.', MessageLevel::WARNING);
        wp_safe_redirect($referer);
        exit;
    }

    $operator = git_operator_by_id((int) ($_POST['id'] ?? -1));

    if ($operator === null) {
        FormOperator::writeMessage('El operador no fue encontrado.', MessageLevel::ERROR);
        wp_safe_redirect($referer);
        exit;
    }

    $operator->getUser()->first_name = $_POST['firstname'];
    $operator->getUser()->last_name = $_POST['lastname'];
    $operator->setPhone($_POST['phone']);
    $operator->setBrandMedia($_POST['brand_media']);
    $operator->setBusinessPlan(
        (int) $_POST['coupons_counter']['limit'],
        (int) $_POST['coupons_counter']['index'],
    );
    $coupons = [];
    foreach ($_POST['coupons'] as $coupon_id) {
        $coupon = git_coupon_by_id((int) $coupon_id);
        if ($coupon) {
            $coupons[] = $coupon;
        }
    }
    $operator->setCoupons($coupons);
    $operator->save();

    TableOperators::writeMessage('Operador guardado correctamente.', MessageLevel::SUCCESS);
    wp_safe_redirect(AdminRouter::get_url_for_class(TableOperators::class));
    exit;
}

function git_ajax_edit_route()
{
    $referer = $_POST['_wp_http_referer'] ?? wp_get_referer();
    $nonce = $_POST['_wpnonce'] ?? '';

    if (wp_verify_nonce($nonce, FormRoute::NONCE_ACTION) === false) {
        FormRoute::writeMessage('El identificador del formulario no es válido o ha expirado.', MessageLevel::WARNING);
        wp_safe_redirect($referer);
        exit;
    }

    if (git_current_user_has_role(UserRole::ADMINISTRATOR) === false) {
        FormRoute::writeMessage('No tiene permisos para realizar esta acción.', MessageLevel::ERROR);
        wp_safe_redirect($referer);
        exit;
    }

    $route = git_route_create($_POST);
    $saved = $route->save();

    if ($saved) {
        TableRoutes::writeMessage('Se a guardado la ruta exitosamente.', MessageLevel::SUCCESS);
        $referer = AdminRouter::get_url_for_class(TableRoutes::class);
    } else {
        FormRoute::writeMessage('Ha ocurrido un error a la hora de guardar la ruta.', MessageLevel::ERROR);
    }

    wp_safe_redirect($referer);
    exit;
}

function git_ajax_edit_service()
{
    $nonce = $_POST['git_nonce'] ?? '';

    if (git_verify_nonce($nonce) === false) {
        FormService::writeMessage('El identificador del formulario no es válido o ha expirado.', MessageLevel::WARNING);
        wp_safe_redirect(AdminRouter::get_url_for_class(FormService::class));
        exit;
    }

    if (git_current_user_has_role(UserRole::ADMINISTRATOR) === false) {
        FormService::writeMessage('No tiene permisos para realizar esta acción.', MessageLevel::ERROR);
        wp_safe_redirect(AdminRouter::get_url_for_class(FormService::class));
        exit;
    }

    $service = git_service_create($_POST);
    $saved = $service->save();

    if ($saved) {
        TableServices::writeMessage('Servicio guardado correctamente.', MessageLevel::SUCCESS);
    } else {
        FormService::writeMessage('Error al guardar el servicio.', MessageLevel::ERROR);
    }

    wp_safe_redirect(AdminRouter::get_url_for_class(TableServices::class));
    exit;
}

function git_ajax_edit_coupon()
{
    $nonce = $_POST['git_nonce'] ?? '';
    $referer = $_POST['git_referer'] ?? wp_get_referer();

    if (git_verify_nonce($nonce) === false) {
        FormCoupon::writeMessage('El identificador del formulario no es válido o ha expirado.', MessageLevel::WARNING);
        wp_safe_redirect(AdminRouter::get_url_for_class(TableZones::class));
        exit;
    }

    if (git_current_user_has_role(UserRole::ADMINISTRATOR) === false) {
        FormCoupon::writeMessage('No tiene permisos para realizar esta acción.', MessageLevel::ERROR);
        wp_safe_redirect($referer);
        exit;
    }

    $coupon_id = $_POST['id'] ?? '0';
    $brand_media = $_POST['brand_media'] ?? '';
    $coupon = git_coupon_by_id((int) ($coupon_id));

    if ($coupon === null) {
        FormCoupon::writeMessage('No se encontro el cupon.', MessageLevel::ERROR);
        wp_safe_redirect(AdminRouter::get_url_for_class(TableZones::class));
        exit;
    }

    git_assign_url_brand_media_to_coupon($coupon, $brand_media);

    TableCoupons::writeMessage('El cupon se actualizo exitosamente', MessageLevel::SUCCESS);
    wp_safe_redirect(AdminRouter::get_url_for_class(TableCoupons::class));
    exit;
}

function git_ajax_edit_zone()
{
    $nonce = $_POST['git_nonce'] ?? '';
    if (git_verify_nonce($nonce) === false) {
        FormZone::writeMessage('El identificador del formulario no es válido o ha expirado.', MessageLevel::WARNING);
        wp_safe_redirect(AdminRouter::get_url_for_class(FormZone::class));
        exit;
    }
    if (git_current_user_has_role(UserRole::ADMINISTRATOR) === false) {
        FormZone::writeMessage('No tiene permisos para realizar esta acción.', MessageLevel::ERROR);
        wp_safe_redirect(AdminRouter::get_url_for_class(FormZone::class));
        exit;
    }
    $zone = git_zone_create($_POST);
    $zone->save();
    TableLocations::writeMessage('Zona guardada correctamente.', MessageLevel::SUCCESS);
    wp_safe_redirect(AdminRouter::get_url_for_class(TableLocations::class));
    exit;
}

function git_ajax_edit_ticket()
{
    $nonce = $_POST['git_nonce'] ?? '';

    if (git_verify_nonce($nonce) === false) {
        FormTicket::writeMessage('El identificador del formulario no es válido o ha expirado.', MessageLevel::WARNING);
        wp_safe_redirect(AdminRouter::get_url_for_class(FormTicket::class));
        exit;
    }

    if (git_current_user_has_role(UserRole::ADMINISTRATOR) === false) {
        FormTicket::writeMessage('No tiene permisos para realizar esta acción.', MessageLevel::ERROR);
        wp_safe_redirect(AdminRouter::get_url_for_class(FormTicket::class));
        exit;
    }

    $ticket = git_ticket_create($_POST);
    $saved = $ticket->save();

    if ($saved === true) {
        TableTickets::writeMessage('Ticket guardado correctamente.', MessageLevel::SUCCESS);
        wp_safe_redirect(AdminRouter::get_url_for_class(TableTickets::class));
        exit;
    }

    FormTicket::writeMessage('Error al guardar el ticket.', MessageLevel::ERROR);
    wp_safe_redirect(AdminRouter::get_url_for_class(FormTicket::class));
    exit;
}

function git_ajax_finish_trip()
{
    $redirect = $_POST['_wp_http_referer'] ?? wp_get_referer();
    $nonce = $_POST['_wpnonce'] ?? '';

    if (wp_verify_nonce($nonce, TableTrip::NONCE_ACTION) === false) {
        TableTrip::writeMessage('El identificador del formulario no es válido o ha expirado.', MessageLevel::WARNING);
        wp_safe_redirect($redirect);
        exit;
    }

    if (git_current_user_has_role(UserRole::ADMINISTRATOR) === false && git_current_user_has_role(UserRole::OPERATOR) === false) {
        TableTrip::writeMessage('No tienes permisos para finalizar el viaje.', MessageLevel::ERROR);
        wp_safe_redirect($redirect);
        exit;
    }

    $passengers = git_passengers($_POST);
    foreach ($passengers as $passenger) {
        $passenger->served = true;
        $passenger->save();

        ob_start();
        $url = AdminRouter::get_url_for_class(TablePassengers::class, ['id' => $passenger->id]);
        ?>
        <p>
            El <a target="_blank" href="<?= esc_url($url) ?>">pasajero con el ID <?= $passenger->id ?></a> ha sido
            transportado.<br>
            El responsable del traslado es <code><?= wp_get_current_user()->user_login ?></code>.
        </p>
        <?php
        git_log_create(
            source: LogSource::PASSENGER,
            id_source: $passenger->id,
            message: ob_get_clean(),
            level: LogLevel::INFO,
        );
    }
    TableTrip::writeMessage('Se han finalizado los viajes de los pasajeros seleccionados.', MessageLevel::SUCCESS);
    wp_safe_redirect($redirect);
    exit;
}

function git_ajax_pdf_trip()
{
    $redirect = $_POST['_wp_http_referer'] ?? wp_get_referer();
    $nonce = $_POST['_wpnonce'] ?? '';

    if (wp_verify_nonce($nonce, TableTrip::NONCE_ACTION) === false) {
        TableTrip::writeMessage('El identificador del formulario no es válido o ha expirado.', MessageLevel::WARNING);
        wp_safe_redirect($redirect);
        exit;
    }

    if (git_current_user_has_role(UserRole::ADMINISTRATOR) === false && git_current_user_has_role(UserRole::OPERATOR) === false) {
        TableTrip::writeMessage('No tienes permisos para finalizar el viaje.', MessageLevel::ERROR);
        wp_safe_redirect($redirect);
        exit;
    }

    $route = git_route_by_id((int) ($_POST['id_route'] ?? -1));
    $transport = git_transport_by_id((int) ($_POST['id_transport'] ?? -1));
    $date_trip = git_date_create($_POST['date_trip'] ?? '');
    $document = new DocumentTrip($route, $transport, $date_trip);

    (new DocumentPdf($document))->renderPdf();
    exit;
}

function git_ajax_pdf_salling_request()
{
    $redirect = $_POST['_wp_http_referer'] ?? wp_get_referer();
    $nonce = $_POST['_wpnonce'] ?? '';

    if (wp_verify_nonce($nonce, TableTrip::NONCE_ACTION) === false) {
        TableTrip::writeMessage('El identificador del formulario no es válido o ha expirado.', MessageLevel::WARNING);
        wp_safe_redirect($redirect);
        exit;
    }

    if (git_current_user_has_role(UserRole::ADMINISTRATOR) === false && git_current_user_has_role(UserRole::OPERATOR) === false) {
        TableTrip::writeMessage('No tienes permisos para finalizar el viaje.', MessageLevel::ERROR);
        wp_safe_redirect($redirect);
        exit;
    }

    $route = git_route_by_id((int) ($_POST['id_route'] ?? -1));
    $transport = git_transport_by_id((int) ($_POST['id_transport'] ?? -1));
    $date_trip = git_date_create($_POST['date_trip'] ?? '');
    $document = new DocumentSallingRequest($route, $transport, $date_trip);

    (new DocumentPdf($document))->renderPdf();
    exit;
}

function git_ajax_transport_maintenance()
{
    $nonce = $_POST['git_nonce'] ?? '';
    if (git_verify_nonce($nonce) === false) {
        TableTransports::writeMessage('El identificador del formulario no es válido o ha expirado.', MessageLevel::ERROR);
        wp_safe_redirect(AdminRouter::get_url_for_class(TableTransports::class));
        exit;
    }

    if (git_current_user_has_role(UserRole::ADMINISTRATOR) === false) {
        TableTransports::writeMessage('No tiene permisos para realizar esta acción.', MessageLevel::ERROR);
        wp_safe_redirect(AdminRouter::get_url_for_class(TableTransports::class));
        exit;
    }

    $transport = git_transport_by_id((int) ($_POST['id'] ?? -1));
    if ($transport === null) {
        TableTransports::writeMessage('Transporte no existe en la base de datos.', MessageLevel::ERROR);
        wp_safe_redirect(AdminRouter::get_url_for_class(TableTransports::class));
        exit;
    }

    $date_start = git_date_create($_POST['date_start'] ?? '');
    $date_end = git_date_create($_POST['date_end'] ?? '');
    $result = $transport->setMaintenanceDates($date_start, $date_end);

    if ($result === true) {
        $transport->getRoutes();
        $transport->getServices();
        $transport->save();
        TableTransports::writeMessage('Disponibilidad del transporte <i>' . $transport->nicename . '</i> actualizada correctamente.', MessageLevel::SUCCESS);
        wp_safe_redirect(AdminRouter::get_url_for_class(TableTransports::class));
        exit;
    }

    if ($result === ErrorService::INVALID_DATE_RANGE) {
        TableTransports::writeMessage('El rango de fechas indicado no es válido.', MessageLevel::ERROR);
        wp_safe_redirect(AdminRouter::get_url_for_class(TableTransports::class));
        exit;
    }

    if ($result === ErrorService::PASSENGERS_PENDING_TRIPS) {
        $url = AdminRouter::get_url_for_class(TablePassengers::class, [
            'served' => false,
            'approved' => true,
            'ticket_status_not' => TicketStatus::PERORDER->slug(),
            'date_trip_from' => $_POST['date_start'],
            'date_trip_to' => $_POST['date_end'],
            'id_transport' => $_POST['id_transport'],
        ]);
        TableTransports::writeMessage('El transporte tiene pasajeros pendientes de viaje. <a target="_blank" href="' . $url . '">Ver pasajeros</a>.', MessageLevel::ERROR);
        wp_safe_redirect(AdminRouter::get_url_for_class(TableTransports::class));
        exit;
    }

    TableTransports::writeMessage('Error al actualizar la disponibilidad del transporte.', MessageLevel::ERROR);
    wp_safe_redirect(AdminRouter::get_url_for_class(TableTransports::class));
    exit;
}

function git_ajax_edit_webhook()
{
    $nonce = $_POST['git_nonce'] ?? '';

    if (git_verify_nonce($nonce) === false) {
        FormWebhook::writeMessage('El identificador del formulario no es válido o ha expirado.', MessageLevel::ERROR);
        wp_safe_redirect(AdminRouter::get_url_for_class(FormWebhook::class));
        exit;
    }

    if (git_current_user_has_role(UserRole::ADMINISTRATOR) === false) {
        FormWebhook::writeMessage('No tienes los permisos para realizar esta acción.', MessageLevel::ERROR);
        wp_safe_redirect(AdminRouter::get_url_for_class(FormWebhook::class));
        exit;
    }

    $webhook = git_webhook_create($_POST);
    $saved = $webhook->save();

    if ($saved === true) {
        FormWebhook::writeMessage('Se ha guardado el webhook correctamente.', MessageLevel::SUCCESS);
        wp_safe_redirect(AdminRouter::get_url_for_class(SettingsWebhooks::class));
        exit;
    }

    FormWebhook::writeMessage('Ha ocurrido un error al guardar el webhook.', MessageLevel::ERROR);
    wp_safe_redirect(AdminRouter::get_url_for_class(FormWebhook::class));
    exit;
}

function git_ajax_export_data()
{
    $nonce = $_POST['git_nonce'] ?? '';
    $referer = $_POST['_wp_http_referer'] ?? wp_get_referer();

    if (git_verify_nonce($nonce) === false) {
        SettingsGeneral::writeMessage('Token de seguridad inválido.', MessageLevel::ERROR);
        wp_safe_redirect($referer);
        exit;
    }

    if (git_current_user_has_role(UserRole::ADMINISTRATOR) === false) {
        SettingsGeneral::writeMessage('No tienes permisos para realizar esta acción.', MessageLevel::ERROR);
        wp_safe_redirect($referer);
        exit;
    }

    $data = (new Migration)->get_data(
        isset($_POST['settings_data']),
        isset($_POST['entities_data']),
        isset($_POST['products_data']),
    );

    $content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    $filename = 'data.json';

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($content));

    echo $content;
    exit;
}

function git_ajax_import_data()
{
    $nonce = $_POST['_wpnonce'] ?? '';
    $referer = $_POST['_wp_http_referer'] ?? wp_get_referer();

    if (wp_verify_nonce($nonce, SettingsGeneral::ACTION_NONCE) === false) {
        SettingsGeneral::writeMessage('Token de seguridad inválido.', MessageLevel::ERROR);
        wp_safe_redirect($referer);
        exit;
    }

    if (git_current_user_has_role(UserRole::ADMINISTRATOR) === false) {
        SettingsGeneral::writeMessage('No tienes permisos para realizar esta acción.', MessageLevel::ERROR);
        wp_safe_redirect($referer);
        exit;
    }

    if (!isset($_FILES['git_data'])) {
        SettingsGeneral::writeMessage('No has subido el archivo de datos.', MessageLevel::ERROR);
        wp_safe_redirect($referer);
        exit;
    }

    $file = $_FILES['git_data'];

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        SettingsGeneral::writeMessage('Ha ocurrido un error a la hora de subir el archivo de configuraciones. Por favor, inténtelo nuevamente.', MessageLevel::ERROR);
        wp_safe_redirect($referer);
        exit;
    }

    $tmp = $file['tmp_name'];
    if (!is_uploaded_file($tmp) || !is_readable($tmp)) {
        SettingsGeneral::writeMessage('Ha ocurrido un error a la hora de subir el archivo de configuraciones. Por favor, inténtelo nuevamente.', MessageLevel::ERROR);
        wp_safe_redirect($referer);
        exit;
    }

    $content = file_get_contents($tmp);
    if ($content === false) {
        SettingsGeneral::writeMessage('No ha sido posible leer el contenido del archivo.', MessageLevel::ERROR);
        wp_safe_redirect($referer);
        exit;
    }
    $lines = explode(PHP_EOL, $content);
    $payload = array_filter(array_map('trim', $lines));

    (new Migration)->set_data($payload);

    SettingsGeneral::writeMessage('Se han ingresado datos al sistema.', MessageLevel::INFO);
    wp_safe_redirect($referer);
    exit;
}

function git_ajax_settings_booking()
{
    $nonce = $_POST['git_nonce'] ?? '';

    if (git_verify_nonce($nonce) === false) {
        SettingsBooking::writeMessage('Token de seguridad inválido.', MessageLevel::ERROR);
        wp_safe_redirect(AdminRouter::get_url_for_class(SettingsBooking::class));
        exit;
    }

    if (git_current_user_has_role(UserRole::ADMINISTRATOR) === false) {
        SettingsBooking::writeMessage('No tienes permisos para realizar esta acción.', MessageLevel::ERROR);
        wp_safe_redirect(AdminRouter::get_url_for_class(SettingsBooking::class));
        exit;
    }

    $kid_message = $_POST[SettingsKeys::FORM_MESSAGE_KID] ?? '';
    $rpm_message = $_POST[SettingsKeys::FORM_MESSAGE_RPM] ?? '';
    $extra_message = $_POST[SettingsKeys::FORM_MESSAGE_EXTRA] ?? '';
    $local_message = $_POST[SettingsKeys::FORM_MESSAGE_LOCAL] ?? '';
    $request_seats = $_POST[SettingsKeys::FORM_MESSAGE_REQUEST_SEATS] ?? '';
    $standard_message = $_POST[SettingsKeys::FORM_MESSAGE_STANDARD] ?? '';
    $flexible_message = $_POST[SettingsKeys::FORM_MESSAGE_FLEXIBLE] ?? '';
    $terms_conditions = $_POST[SettingsKeys::FORM_MESSAGE_TERMS_CONDITIONS] ?? '';
    $days_without_sale = (int) ($_POST[SettingsKeys::FORM_DAYS_WITHOUT_SALE] ?? 0);

    git_set_setting(SettingsKeys::FORM_MESSAGE_KID, $kid_message);
    git_set_setting(SettingsKeys::FORM_MESSAGE_RPM, $rpm_message);
    git_set_setting(SettingsKeys::FORM_MESSAGE_EXTRA, $extra_message);
    git_set_setting(SettingsKeys::FORM_MESSAGE_LOCAL, $local_message);
    git_set_setting(SettingsKeys::FORM_MESSAGE_STANDARD, $standard_message);
    git_set_setting(SettingsKeys::FORM_MESSAGE_FLEXIBLE, $flexible_message);
    git_set_setting(SettingsKeys::FORM_DAYS_WITHOUT_SALE, $days_without_sale);
    git_set_setting(SettingsKeys::FORM_MESSAGE_REQUEST_SEATS, $request_seats);
    git_set_setting(SettingsKeys::FORM_MESSAGE_TERMS_CONDITIONS, $terms_conditions);

    SettingsBooking::writeMessage('Se han guardado los cambios correctamente.', MessageLevel::SUCCESS);
    wp_safe_redirect(AdminRouter::get_url_for_class(SettingsBooking::class));
    exit;
}

function git_ajax_settings_notifications()
{
    $redirect_url = $_POST['_wp_http_referer'] ?? wp_get_referer();
    $nonce = $_POST['_wpnonce'] ?? '';

    if (wp_verify_nonce($nonce, SettingsNotifications::ACTION_NONCE) === false) {
        SettingsNotifications::writeMessage('Token de seguridad inválido.', MessageLevel::ERROR);
        wp_safe_redirect($redirect_url);
        exit;
    }

    if (git_current_user_has_role(UserRole::ADMINISTRATOR) === false) {
        SettingsNotifications::writeMessage('No tienes permisos para realizar esta acción.', MessageLevel::ERROR);
        wp_safe_redirect($redirect_url);
        exit;
    }

    $notification_checkout_message = $_POST['notification_checkout_message'] ?? '';
    $notification_checkout_email_title = $_POST['notification_checkout_email_title'] ?? '';
    $notification_checkout_email_sender = $_POST['notification_checkout_email_sender'] ?? '';
    $notification_checkout_email_content = $_POST['notification_checkout_email_content'] ?? '';

    git_set_setting(SettingsKeys::NOTIFICATION_CHECKOUT_MESSAGE, $notification_checkout_message);
    git_set_setting(SettingsKeys::NOTIFICATION_CHECKOUT_EMAIL_TITLE, $notification_checkout_email_title);
    git_set_setting(SettingsKeys::NOTIFICATION_CHECKOUT_EMAIL_SENDER, $notification_checkout_email_sender);
    git_set_setting(SettingsKeys::NOTIFICATION_CHECKOUT_EMAIL_CONTENT, $notification_checkout_email_content);

    SettingsNotifications::writeMessage('Se han guardado los cambios correctamente.', MessageLevel::SUCCESS);
    wp_safe_redirect($redirect_url);
    exit;
}

function git_ajax_settings_general()
{
    $nonce = $_POST['git_nonce'] ?? '';

    if (git_verify_nonce($nonce) === false) {
        SettingsGeneral::writeMessage('Token de seguridad inválido.', MessageLevel::ERROR);
        wp_safe_redirect(AdminRouter::get_url_for_class(SettingsGeneral::class));
        exit;
    }

    if (git_current_user_has_role(UserRole::ADMINISTRATOR) === false) {
        SettingsGeneral::writeMessage('No tienes permisos para realizar esta acción.', MessageLevel::ERROR);
        wp_safe_redirect(AdminRouter::get_url_for_class(SettingsGeneral::class));
        exit;
    }

    $operator_file_size = $_POST[SettingsKeys::GENERAL_FILE_SIZE] ?? 1;
    $operator_profile_page = $_POST[SettingsKeys::GENERAL_PROFILE_PAGE] ?? 1;
    $operator_file_extensions = $_POST[SettingsKeys::GENERAL_FILE_EXTENSION] ?? '';

    git_set_setting(SettingsKeys::GENERAL_FILE_SIZE, (int) $operator_file_size);
    git_set_setting(SettingsKeys::GENERAL_PROFILE_PAGE, (int) $operator_profile_page);
    git_set_setting(SettingsKeys::GENERAL_FILE_EXTENSION, git_sanitize_file_extensions($operator_file_extensions));

    SettingsGeneral::writeMessage('Se han guardado los cambios correctamente.', MessageLevel::SUCCESS);
    wp_safe_redirect(AdminRouter::get_url_for_class(SettingsGeneral::class));
    exit;
}

function git_ajax_settings_secret_key()
{
    $redirect_url = AdminRouter::get_url_for_class(SettingsGeneral::class);
    $nonce = $_POST['_gitnonce'] ?? '';

    if (git_verify_nonce($nonce) === false) {
        SettingsGeneral::writeMessage('Token de seguridad inválido.', MessageLevel::ERROR);
        wp_safe_redirect($redirect_url);
        exit;
    }

    if (git_current_user_has_role(UserRole::ADMINISTRATOR) === false) {
        SettingsGeneral::writeMessage('No tienes permisos para realizar esta acción.', MessageLevel::ERROR);
        wp_safe_redirect($redirect_url);
        exit;
    }

    SettingsGeneral::writeMessage('Se ha generado una nueva clave secreta.', MessageLevel::SUCCESS);
    $lengths = [16, 24, 32];
    $length = $lengths[array_rand($lengths)];
    git_set_secret_key(random_bytes($length));
    wp_safe_redirect($redirect_url);
    exit;
}

function git_ajax_settings_labels()
{
    $nonce = $_POST['_wpnonce'] ?? '';
    $redirect_url = $_POST['_wp_http_referer'] ?? wp_get_referer();

    if (wp_verify_nonce($nonce, SettingsLabels::ACTION_NONCE) === false) {
        SettingsLabels::writeMessage('Token de seguridad inválido.', MessageLevel::ERROR);
        wp_safe_redirect($redirect_url);
        exit;
    }

    if (git_current_user_has_role(UserRole::ADMINISTRATOR) === false) {
        SettingsLabels::writeMessage('No tienes permisos para realizar esta acción.', MessageLevel::ERROR);
        wp_safe_redirect($redirect_url);
        exit;
    }

    $label_ticket_cancel = $_POST['label_ticket_cancel'] ?? '';
    $label_ticket_payment = $_POST['label_ticket_payment'] ?? '';
    $label_ticket_partial = $_POST['label_ticket_partial'] ?? '';
    $label_ticket_pending = $_POST['label_ticket_pending'] ?? '';

    $label_transport_a = $_POST['label_transport_a'] ?? '';
    $label_transport_b = $_POST['label_transport_b'] ?? '';
    $label_transport_c = $_POST['label_transport_c'] ?? '';

    $label_route_one_way = $_POST['label_route_one_way'] ?? '';
    $label_route_any_way = $_POST['label_route_any_way'] ?? '';
    $label_route_double_way = $_POST['label_route_double_way'] ?? '';

    git_set_setting(SettingsKeys::LABEL_TICKET_CANCEL, $label_ticket_cancel);
    git_set_setting(SettingsKeys::LABEL_TICKET_PAYMENT, $label_ticket_payment);
    git_set_setting(SettingsKeys::LABEL_TICKET_PARTIAL, $label_ticket_partial);
    git_set_setting(SettingsKeys::LABEL_TICKET_PENDING, $label_ticket_pending);
    git_set_setting(SettingsKeys::LABEL_TRANSPORT_A, $label_transport_a);
    git_set_setting(SettingsKeys::LABEL_TRANSPORT_B, $label_transport_b);
    git_set_setting(SettingsKeys::LABEL_TRANSPORT_C, $label_transport_c);
    git_set_setting(SettingsKeys::LABEL_ROUTE_ONE_WAY, $label_route_one_way);
    git_set_setting(SettingsKeys::LABEL_ROUTE_ANY_WAY, $label_route_any_way);
    git_set_setting(SettingsKeys::LABEL_ROUTE_DOUBLE_WAY, $label_route_double_way);

    SettingsLabels::writeMessage('Se han guardado los cambios correctamente.', MessageLevel::SUCCESS);
    wp_safe_redirect($redirect_url);
    exit;
}

function git_ajax_settings_ticket_viewer()
{
    $nonce = $_POST['nonce'] ?? '';
    $redirect_url = $_POST['_wp_http_referer'] ?? wp_get_referer();

    if (wp_verify_nonce($nonce, SettingsViewer::ACTION_NONCE) === false) {
        SettingsBooking::writeMessage('Token de seguridad inválido.', MessageLevel::ERROR);
        wp_safe_redirect($redirect_url);
        exit;
    }

    if (git_current_user_has_role(UserRole::ADMINISTRATOR) === false) {
        SettingsBooking::writeMessage('No tienes permisos para realizar esta acción.', MessageLevel::ERROR);
        wp_safe_redirect($redirect_url);
        exit;
    }

    $ticket_viewer = $_POST['ticket_viewer'] ?? -1;
    $ticket_viewer_js = $_POST['ticket_viewer_js'] ?? '';
    $ticket_viewer_css = $_POST['ticket_viewer_css'] ?? '';
    $ticket_viewer_html = $_POST['ticket_viewer_html'] ?? '';
    $ticket_viewer_default_media = $_POST['ticket_viewer_default_media'] ?? '';
    $ticket_viewer_passenger_html = $_POST['ticket_viewer_passenger_html'] ?? '';

    git_set_setting(SettingsKeys::TICKET_VIEWER, (int) $ticket_viewer);
    git_set_setting(SettingsKeys::TICKET_VIEWER_JS, $ticket_viewer_js);
    git_set_setting(SettingsKeys::TICKET_VIEWER_CSS, $ticket_viewer_css);
    git_set_setting(SettingsKeys::TICKET_VIEWER_HTML, $ticket_viewer_html);
    git_set_setting(SettingsKeys::TICKET_VIEWER_DEFAULT_MEDIA, $ticket_viewer_default_media);
    git_set_setting(SettingsKeys::TICKET_VIEWER_PASSENGER_HTML, $ticket_viewer_passenger_html);

    wp_safe_redirect(AdminRouter::get_url_for_class(SettingsBooking::class));
    SettingsBooking::writeMessage('Se han guardado los cambios correctamente.', MessageLevel::SUCCESS);
    exit;
}

function git_ajax_download_invoice_csv()
{
    $nonce = $_POST['_gitnonce'] ?? '';

    if (!git_verify_nonce($nonce)) {
        exit;
    }

    $operator = git_operator_by_id($_POST['operator'] ?? -1);

    if ($operator === null) {
        exit;
    }

    $downloader = new DownloadInvoiceInfo();

    $downloader->download_csv(
        $operator,
        $_POST['date_start'] ?? date('Y-m-01'),
        $_POST['date_end'] ?? date('Y-m-t'),
        get_post($_POST['coupon'] ?? null),
        $_POST['columns'] ?? [],
    );

    wp_safe_redirect($_POST['_wp_http_referer'] ?? admin_url());
    exit;
}

add_action('wp_ajax_download_invoice_csv', 'git_ajax_download_invoice_csv');

add_action('wp_ajax_git_qr_generator', function () {
    $type = $_POST['type'] ?? 'url';
    if ($type === 'url') {
        $data = git_qr_data_url($_POST['url'] ?? '');
    } else if ($type === 'email') {
        $data = git_qr_data_email(
            $_POST['email'] ?? '',
            $_POST['email_subject'] ?? null,
            $_POST['email_message'] ?? null
        );
    } elseif ($type === 'phone') {
        $data = git_qr_data_phone($_POST['phone'] ?? '');
    } elseif ($type === 'whatsapp') {
        $data = git_qr_data_whatsapp(
            $_POST['whatsapp_phone'] ?? '',
            $_POST['whatsapp_message']
        );
    } elseif ($type === 'wifi') {
        $ssid = $_POST['ssid'] ?? '';
        $password = $_POST['password'] ?? '';
        $encryption = $_POST['encryption'] ?? 'WPA';
        $hidden = isset($_POST['hidden']);
        $data = get_qr_data_wifi($ssid, $password, $encryption, $hidden);
    } else {
        $data = git_qr_data('__EMPTY__');
    }
    $size = $_POST['size'] ?? '100';
    $margin = $_POST['margin'] ?? '100';
    $ecc = $_POST['ecc'] ?? ErrorCorrectionCode::LOW;
    $color = $_POST['color'] ?? '#000000';
    $bgColor = $_POST['bgcolor'] ?? '#FFFFFF';
    try {
        $codeQr = git_qr_code($data, [
            'size' => (int) $size,
            'margin' => (int) $margin,
            'color_hex' => (string) $color,
            'bg_color_hex' => (string) $bgColor,
            'ecc' => $ecc,
        ]);
        wp_send_json_success(['qr_html' => $codeQr->compact('Código QR generador por SUP Galápagos')]);
    } catch (Exception $e) {
        git_set_setting('sample', $e->getMessage());
        wp_send_json_error(['message' => 'Error al generar los datos del código QR: ' . $e->getMessage()]);
        return;
    }
});

function git_ajax_edit_ticket_operator()
{
    $refereder = $_POST['_wp_http_referer'] ?? wp_get_referer();

    if (git_verify_nonce($_POST['_gitnonce'] ?? '') === false) {
        wp_safe_redirect($refereder);
        exit;
    }

    $origin = (int) ($_POST['origin'] ?? '0');
    $destiny = (int) ($_POST['destiny'] ?? '0');
    $departure_time = (string) ($_POST['departure_time'] ?? '');
    $routes = git_routes([
        'id_origin' => $origin,
        'id_destiny' => $destiny,
        'departure_time' => $departure_time,
    ]);

    if (count($routes) === 0) {
        wp_safe_redirect($refereder);
        exit;
    }

    $route = $routes[0];
    $transport = git_transport_by_id((int) ($_POST['transport'] ?? '0'));

    if ($transport === null) {
        wp_safe_redirect($refereder);
        exit;
    }

    $passengers = [];

    for ($i = 0; $i < (int) $_POST['passengers']; $i++) {
        $passenger = git_passenger_create([
            'route' => $route,
            'transport' => $transport,
            'date_trip' => $_POST['date_trip'] ?? '',
        ]);
        $passengers[] = $passenger;
    }

    $ticket = git_ticket_create([
        'passengers' => $passengers,
        'flexible' => false,
        'status' => TicketStatus::PERORDER,
        'meta' => [
            'date_created' => date('Y-m-d H:i:s'),
        ]
    ]);

    $ticket->save();

    wp_safe_redirect($refereder);
    exit;
}

add_action('wp_ajax_git_edit_ticket_operator', 'git_ajax_edit_ticket_operator');

function git_ajax_passenger_transfer_list()
{
    if (git_verify_nonce($_POST['nonce'] ?? '') === false) {
        wp_send_json_error(['message' => 'Token de seguridad inválido.']);
        exit;
    }

    $passenger_id = (int) ($_POST['id'] ?? 0);
    $add = $_POST['add_transfer'] === '1';

    if ($add) {
        FormTransfer::addListPassenger($passenger_id);
        wp_send_json_success([
            'message' => 'Pasajeros agregados correctamente.',
            'in_list' => true
        ]);
    } else {
        FormTransfer::removePassengerInList($passenger_id);
        wp_send_json_success([
            'message' => 'Pasajeros removidos correctamente.',
            'in_list' => false
        ]);
    }
    exit;
}

function git_ajax_login()
{
    $nonce = $_POST['git_nonce'] ?? '';
    $redirect = $_POST['_wp_http_referer'] ?? '';
    $messenger = new MessageTemporal;

    if (git_verify_nonce($nonce) === false) {
        $messenger->writeTemporalMessage(
            'Solicitud inválida.',
            ProfileDashboard::class,
            MessageLevel::ERROR
        );
        wp_safe_redirect($redirect);
        exit;
    }

    $username = sanitize_user($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['rememberme']);

    $user = wp_authenticate($username, $password);

    if (is_wp_error($user)) {
        $messenger->writeTemporalMessage(
            'Usuario o contraseña no válida.',
            ProfileDashboard::class,
            MessageLevel::ERROR
        );
        wp_safe_redirect($redirect);
        exit;
    }

    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, $remember);

    $messenger->writeTemporalMessage(
        'Benvenido ' . $user->user_nicename . '.',
        ProfileDashboard::class,
        MessageLevel::INFO
    );
    wp_safe_redirect($redirect);
    exit;
}

# Register AJAX actions
add_action('wp_ajax_git_edit_zone', 'git_ajax_edit_zone');
add_action('wp_ajax_git_edit_route', 'git_ajax_edit_route');
add_action('wp_ajax_git_edit_coupon', 'git_ajax_edit_coupon');
add_action('wp_ajax_git_edit_ticket', 'git_ajax_edit_ticket');
add_action('wp_ajax_git_edit_service', 'git_ajax_edit_service');
add_action('wp_ajax_git_edit_webhook', 'git_ajax_edit_webhook');
add_action('wp_ajax_git_edit_location', 'git_ajax_edit_location');
add_action('wp_ajax_git_edit_operator', 'git_ajax_edit_operator');
add_action('wp_ajax_git_edit_transport', 'git_ajax_edit_transport');
add_action('wp_ajax_git_edit_ticket_status', 'git_ajax_edit_ticket_status');

add_action('wp_ajax_git_pdf_trip', 'git_ajax_pdf_trip');
add_action('wp_ajax_git_finish_trip', 'git_ajax_finish_trip');
add_action('wp_ajax_git_pdf_salling_request', 'git_ajax_pdf_salling_request');
add_action('wp_ajax_git_transport_maintenance', 'git_ajax_transport_maintenance');

add_action('wp_ajax_git_settings_labels', 'git_ajax_settings_labels');
add_action('wp_ajax_git_settings_booking', 'git_ajax_settings_booking');
add_action('wp_ajax_git_settings_general', 'git_ajax_settings_general');
add_action('wp_ajax_git_settings_secret_key', 'git_ajax_settings_secret_key');
add_action('wp_ajax_git_setting_ticket_viewer', 'git_ajax_settings_ticket_viewer');
add_action('wp_ajax_git_settings_notifications', 'git_ajax_settings_notifications');

add_action('wp_ajax_git_add_passenger_to_list_transfer', 'git_ajax_passenger_transfer_list');

add_action('wp_ajax_git_export_data', 'git_ajax_export_data');
add_action('wp_ajax_git_import_data', 'git_ajax_import_data');

add_action('wp_ajax_git_login', 'git_ajax_login');
add_action('wp_ajax_nopriv_git_login', 'git_ajax_login');
