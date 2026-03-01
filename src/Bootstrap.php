<?php
namespace CentralBooking;

use CentralBooking\Admin\TestPage;
use CentralBooking\Data\Constants\UserRole;
use CentralBooking\Admin\AdminRouter;
use CentralBooking\Client\TicketViewer;
use CentralBooking\GUI\CompositeComponent;
use CentralBooking\Preorder\PreorderDashboard;
use CentralBooking\Profile\ProfileDashboard;
use CentralBooking\REST\EndpointsPreorder;
use CentralBooking\REST\EndpointsRoosevelt;
use DateTime;

final class Bootstrap
{
    private static ?self $instance = null;
    private static bool $initialized = false;

    private function __construct()
    {
    }

    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function init()
    {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;
        $instance = self::get_instance();
        $instance->init_rest();
        $instance->init_admin_menu();
        $instance->init_admin_shortcuts();
        $instance->init_woocommerce_extensions();
    }

    private function init_rest()
    {
        add_action('rest_api_init', function () {
            (new EndpointsPreorder())->init_endpoints();
            (new EndpointsRoosevelt())->init_endpoints();
        });
    }

    private function init_woocommerce_extensions(): void
    {
        add_filter('product_type_selector', function ($types) {
            $types['operator'] = 'Producto operable';
            return $types;
        });
        add_action('woocommerce_loaded', function () {
            if (class_exists('WC_Product')) {
                require_once CENTRAL_BOOKING_DIR . '/includes/git-class-product-operator.php';
            }
        });
    }

    private function init_admin_shortcuts()
    {
        add_shortcode('git_profile', fn() => (new ProfileDashboard)->compact());
        add_shortcode('git_ticket_preview', function () {
            wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css');
            wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', ['jquery'], null, true);
            return (new TicketViewer($_GET['data'] ?? -1))->compact();
        });

        add_shortcode('git_interactive_map', function ($atts) {
            $attributes = shortcode_atts([
                'width' => '100%',
                'height' => '750px',
                'url' => urlencode(CENTRAL_BOOKING_URL),
                'dir' => CENTRAL_BOOKING_DIR,
            ], $atts);
            $component = new CompositeComponent('iframe');
            $src = add_query_arg([
                'git_url' => $attributes['url'],
                'git_dir' => $attributes['dir'],
            ], CENTRAL_BOOKING_URL . 'includes/git-interactive-map.php');
            $component->attributes->set('src', $src);
            $component->styles->set('width', $attributes['width']);
            $component->styles->set('height', $attributes['height']);
            return $component->compact();
        });

        add_shortcode('git_preorder', fn() => (new PreorderDashboard)->compact());
    }

    private function init_admin_menu()
    {
        add_role(UserRole::OPERATOR->slug(), 'Operador', ['read' => true]);
        add_role(UserRole::MARKETER->slug(), 'Comercializador', ['read' => true]);
        add_action('admin_menu', function () {
            wp_enqueue_style(
                'icons-bootstrap',
                'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css',
                [],
                '5.3.0',
                'all'
            );
            wp_enqueue_style(
                'git-admin-dashboard',
                CENTRAL_BOOKING_URL . '/assets/css/admin-dashboard.css',
            );
            wp_enqueue_script(
                'git-admin-dashboard',
                CENTRAL_BOOKING_URL . '/assets/js/admin-dashboard.js',
                ['jquery'],
            );
            if (current_user_can('manage_options')) {
                add_menu_page(
                    'Central Reservas',
                    'Central Reservas',
                    'manage_options',
                    AdminRouter::PAGE_CENTRAL_BOOKING,
                    function () {
                        AdminRouter::render_page(
                            AdminRouter::PAGE_CENTRAL_BOOKING,
                            $_GET['action'] ?? null,
                        );
                        ?>
                    <div class="notice notice-info is-dismissible" style="padding:16px; margin-bottom:16px;">
                        <h2 style="margin-top:0;">Central Reservas - Versión del Plugin</h2>
                        <p>
                            <strong>Versión actual:</strong> 1.0
                        </p>
                        <p style="color:#666;">
                            Última actualización: <?= git_date_format((new DateTime('@' . filemtime(__FILE__)))->format('Y-m-d')) ?> </p>
                    </div>
                    <?php
                    },
                    'dashicons-tickets',
                    6
                );
                add_submenu_page(
                    AdminRouter::PAGE_CENTRAL_BOOKING,
                    'Comercializador',
                    'Comercializador',
                    'manage_options',
                    AdminRouter::PAGE_MARKETING,
                    function () {
                        AdminRouter::render_page(
                            AdminRouter::PAGE_MARKETING,
                            $_GET['action'] ?? null,
                        );
                    }
                );
                add_submenu_page(
                    AdminRouter::PAGE_CENTRAL_BOOKING,
                    'Pasajeros',
                    'Pasajeros',
                    'manage_options',
                    AdminRouter::PAGE_PASSENGERS,
                    function () {
                        AdminRouter::render_page(
                            AdminRouter::PAGE_PASSENGERS,
                            $_GET['action'] ?? null,
                        );
                    }
                );
                add_submenu_page(
                    AdminRouter::PAGE_CENTRAL_BOOKING,
                    'Tickets',
                    'Tickets',
                    'manage_options',
                    AdminRouter::PAGE_TICKETS,
                    function () {
                        AdminRouter::render_page(
                            AdminRouter::PAGE_TICKETS,
                            $_GET['action'] ?? null,
                        );
                    }
                );
                add_submenu_page(
                    AdminRouter::PAGE_CENTRAL_BOOKING,
                    'Transportes',
                    'Transportes',
                    'manage_options',
                    AdminRouter::PAGE_TRANSPORTS,
                    function () {
                        AdminRouter::render_page(
                            AdminRouter::PAGE_TRANSPORTS,
                            $_GET['action'] ?? null,
                        );
                    }
                );
                add_submenu_page(
                    AdminRouter::PAGE_CENTRAL_BOOKING,
                    'Rutas',
                    'Rutas',
                    'manage_options',
                    AdminRouter::PAGE_ROUTES,
                    function () {
                        AdminRouter::render_page(
                            AdminRouter::PAGE_ROUTES,
                            $_GET['action'] ?? null,
                        );
                    }
                );
                add_submenu_page(
                    AdminRouter::PAGE_CENTRAL_BOOKING,
                    'Servicios',
                    'Servicios',
                    'manage_options',
                    AdminRouter::PAGE_SERVICES,
                    function () {
                        AdminRouter::render_page(
                            AdminRouter::PAGE_SERVICES,
                            $_GET['action'] ?? null,
                        );
                    }
                );
                add_submenu_page(
                    AdminRouter::PAGE_CENTRAL_BOOKING,
                    'Ubicaciones',
                    'Ubicaciones',
                    'manage_options',
                    AdminRouter::PAGE_LOCATIONS,
                    function () {
                        AdminRouter::render_page(
                            AdminRouter::PAGE_LOCATIONS,
                            $_GET['action'] ?? null,
                        );
                    }
                );
                add_submenu_page(
                    AdminRouter::PAGE_CENTRAL_BOOKING,
                    'Operadores',
                    'Operadores',
                    'manage_options',
                    AdminRouter::PAGE_OPERATORS,
                    function () {
                        AdminRouter::render_page(
                            AdminRouter::PAGE_OPERATORS,
                            $_GET['action'] ?? null,
                        );
                    }
                );
                add_submenu_page(
                    AdminRouter::PAGE_CENTRAL_BOOKING,
                    'Logs',
                    'Logs',
                    'manage_options',
                    AdminRouter::PAGE_LOGS,
                    function () {
                        AdminRouter::render_page(
                            AdminRouter::PAGE_LOGS,
                            $_GET['action'] ?? null,
                        );
                    }
                );
                add_submenu_page(
                    AdminRouter::PAGE_CENTRAL_BOOKING,
                    'Campo de pruebas',
                    'Campo de pruebas',
                    'manage_options',
                    'test_field',
                    function () {
                        (new TestPage)->render();
                    }
                );
            }
        });
    }
}