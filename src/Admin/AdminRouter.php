<?php
namespace CentralBooking\Admin;

use CentralBooking\Admin\Form\FormCoupon;
use CentralBooking\Admin\Form\FormLocation;
use CentralBooking\Admin\Form\FormOperator;
use CentralBooking\Admin\Form\FormQRCode;
use CentralBooking\Admin\Form\FormRoute;
use CentralBooking\Admin\Form\FormService;
use CentralBooking\Admin\Form\FormTicket;
use CentralBooking\Admin\Form\FormTransfer;
use CentralBooking\Admin\Form\FormTransport;
use CentralBooking\Admin\Form\FormWebhook;
use CentralBooking\Admin\Form\FormZone;
use CentralBooking\Admin\Setting\SettingsBooking;
use CentralBooking\Admin\Setting\SettingsGeneral;
use CentralBooking\Admin\Setting\SettingsNotifications;
use CentralBooking\Admin\Setting\SettingsLabels;
use CentralBooking\Admin\Setting\SettingsSecretKey;
use CentralBooking\Admin\Setting\SettingsViewer;
use CentralBooking\Admin\Setting\SettingsWebhooks;
use CentralBooking\Admin\View\TableCoupons;
use CentralBooking\Admin\View\TableLocations;
use CentralBooking\Admin\View\TableOperators;
use CentralBooking\Admin\View\TablePassengers;
use CentralBooking\Admin\View\TablePassengersLog;
use CentralBooking\Admin\View\TableRoutes;
use CentralBooking\Admin\View\TableServices;
use CentralBooking\Admin\View\TableTickets;
use CentralBooking\Admin\View\TableTicketsLog;
use CentralBooking\Admin\View\TableTransports;
use CentralBooking\Admin\Setting\SettingsAPIDoc;

final class AdminRouter
{
    public const PAGE_CENTRAL_BOOKING = 'git-central';
    public const PAGE_MARKETING = 'git-marketing';
    public const PAGE_PASSENGERS = 'git-passengers';
    public const PAGE_TICKETS = 'git-tickets';
    public const PAGE_TRANSPORTS = 'git-transports';
    public const PAGE_ROUTES = 'git-routes';
    public const PAGE_SERVICES = 'git-services';
    public const PAGE_LOCATIONS = 'git-locations';
    public const PAGE_OPERATORS = 'git-operators';
    public const PAGE_LOGS = 'git-logs';

    private static array $route_mappings = [
        self::PAGE_CENTRAL_BOOKING => [
            'default_action' => 'general',
            'header' => 'Central Reservas',
            'tabpane' => true,
            'actions' => [
                'general' => [
                    'tab_label' => 'General',
                    'target' => SettingsGeneral::class,
                    'is_tab' => true,
                ],
                'booking' => [
                    'tab_label' => 'Reseva',
                    'target' => SettingsBooking::class,
                    'is_tab' => true,
                ],
                'tickets' => [
                    'tab_label' => 'Visor',
                    'target' => SettingsViewer::class,
                    'is_tab' => true,
                ],
                'labels' => [
                    'tab_label' => 'Etiquetas',
                    'target' => SettingsLabels::class,
                    'is_tab' => true,
                ],
                'api_doc' => [
                    'tab_label' => 'API Doc',
                    'target' => SettingsAPIDoc::class,
                    'is_tab' => true,
                ],
                'webhooks' => [
                    'tab_label' => 'Webhooks',
                    'target' => SettingsWebhooks::class,
                    'is_tab' => true,
                    'redirects' => [
                        [
                            'label' => 'Crear webhook',
                            'to' => FormWebhook::class,
                        ]
                    ],
                ],
                'messenger' => [
                    'tab_label' => 'Notificaciones',
                    'target' => SettingsNotifications::class,
                    'is_tab' => true,
                ],
                'edit_webhook' => [
                    'tab_label' => 'Editar Webhook',
                    'target' => FormWebhook::class,
                    'is_tab' => false,
                    'redirects' => [
                        [
                            'label' => 'Regresar a la lista',
                            'to' => SettingsWebhooks::class,
                        ]
                    ],
                ],
                'secret_key' => [
                    'tab_label' => 'Clave Secreta',
                    'target' => SettingsSecretKey::class,
                    'is_tab' => false,
                    'redirects' => [],
                ],
            ],
        ],
        self::PAGE_MARKETING => [
            'header' => 'Comercializador',
            'default_action' => 'list_flyers',
            'tabpane' => true,
            'actions' => [
                'list_flyers' => [
                    'is_tab' => true,
                    'tab_label' => 'Flyer de Comercializador',
                    'target' => TableCoupons::class,
                    // 'redirects' => [
                    //     [
                    //         'label' => 'Asignar Flyer',
                    //         'to' => FormCoupon::class,
                    //     ]
                    // ],
                ],
                'edit_coupon' => [
                    'is_tab' => false,
                    'target' => FormCoupon::class,
                    'redirects' => [
                        [
                            'label' => 'Lista de Flyers',
                            'to' => TableCoupons::class,
                        ]
                    ],
                ],
                'qr_generator' => [
                    'is_tab' => true,
                    'tab_label' => 'Generador QR',
                    'target' => FormQRCode::class,
                ],
            ],
        ],
        self::PAGE_PASSENGERS => [
            'header' => 'Pasajeros',
            'default_action' => 'list',
            'actions' => [
                'list' => [
                    'target' => TablePassengers::class,
                    'redirects' => [
                        [
                            'label' => 'Modo Traslado',
                            'to' => FormTransfer::class,
                        ]
                    ],
                ],
                'transfer' => [
                    'target' => FormTransfer::class,
                    'redirects' => [
                        [
                            'label' => 'Regresar a la lista',
                            'to' => TablePassengers::class,
                        ]
                    ],
                ],
            ],
        ],
        self::PAGE_TICKETS => [
            'header' => 'Tickets',
            'default_action' => 'list',
            'actions' => [
                'list' => [
                    'target' => TableTickets::class,
                ],
                'edit' => [
                    'target' => FormTicket::class,
                    'redirects' => [
                        [
                            'label' => 'Volver a la lista',
                            'to' => TableTickets::class,
                        ]
                    ],
                ],
            ],
        ],
        self::PAGE_TRANSPORTS => [
            'header' => 'Transportes',
            'default_action' => 'list',
            'actions' => [
                'list' => [
                    'target' => TableTransports::class,
                    'redirects' => [
                        [
                            'label' => 'Añadir nuevo',
                            'to' => FormTransport::class,
                        ]
                    ],
                ],
                'edit' => [
                    'target' => FormTransport::class,
                    'redirects' => [
                        [
                            'label' => 'Regresar a la lista',
                            'to' => TableTransports::class,
                        ]
                    ],
                ],
            ],
        ],
        self::PAGE_ROUTES => [
            'header' => 'Rutas',
            'default_action' => 'list',
            'actions' => [
                'list' => [
                    'target' => TableRoutes::class,
                    'redirects' => [
                        [
                            'label' => 'Añadir nuevo',
                            'to' => FormRoute::class,
                        ]
                    ],
                ],
                'edit' => [
                    'target' => FormRoute::class,
                    'redirects' => [
                        [
                            'label' => 'Regresar a la lista',
                            'to' => TableRoutes::class,
                        ]
                    ],
                ],
            ],
        ],
        self::PAGE_SERVICES => [
            'header' => 'Servicios',
            'default_action' => 'list',
            'actions' => [
                'list' => [
                    'target' => TableServices::class,
                    'redirects' => [
                        [
                            'label' => 'Añadir nuevo',
                            'to' => FormService::class,
                        ]
                    ],
                ],
                'edit' => [
                    'target' => FormService::class,
                    'redirects' => [
                        [
                            'label' => 'Regresar a la lista',
                            'to' => TableServices::class,
                        ]
                    ],
                ],
            ],
        ],
        self::PAGE_LOCATIONS => [
            'header' => 'Ubicaciones',
            'default_action' => 'list',
            'actions' => [
                'list' => [
                    'tab_label' => 'Ubicaciones',
                    'target' => TableLocations::class,
                    'redirects' => [
                        [
                            'label' => 'Nueva locación',
                            'to' => FormLocation::class,
                        ],
                        [
                            'label' => 'Nueva zona',
                            'to' => FormZone::class,
                        ],
                    ],
                ],
                'edit_location' => [
                    'target' => FormLocation::class,
                    'redirects' => [
                        [
                            'label' => 'Regresar a la lista',
                            'to' => TableLocations::class,
                        ]
                    ],
                ],
                'edit_zone' => [
                    'target' => FormZone::class,
                    'redirects' => [
                        [
                            'label' => 'Regresar a la lista',
                            'to' => TableLocations::class,
                        ]
                    ],
                ],
            ],
        ],
        self::PAGE_OPERATORS => [
            'header' => 'Operadores',
            'default_action' => 'list',
            'actions' => [
                'list' => [
                    'target' => TableOperators::class
                ],
                'edit' => [
                    'target' => FormOperator::class,
                    'redirects' => [
                        [
                            'label' => 'Listar operadores',
                            'to' => TableOperators::class,
                        ]
                    ],
                ]
            ],
        ],
        self::PAGE_LOGS => [
            'header' => 'Logs',
            'tabpane' => true,
            'default_action' => 'list_tickets',
            'actions' => [
                'list_tickets' => [
                    'tab_label' => 'Tickets',
                    'target' => TableTicketsLog::class,
                    'is_tab' => true,
                ],
                'list_passengers' => [
                    'tab_label' => 'Pasajeros',
                    'target' => TablePassengersLog::class,
                    'is_tab' => true,
                ],
            ],
        ],
    ];

    public static function get_route_for_class(string $classname): ?array
    {
        foreach (self::$route_mappings as $page => $config) {
            foreach ($config['actions'] as $action => $class) {
                if (!isset($class['target'])) {
                    continue;
                }
                if ($class['target'] === $classname) {
                    return [
                        'page' => $page,
                        'action' => $action
                    ];
                }
            }
        }
        return null;
    }

    /**
     * Obtener URL para una clase específica
     */
    public static function get_url_for_class(string $classname, array $additional_params = []): string
    {
        $route = self::get_route_for_class($classname);
        if (!$route) {
            return '';
        }

        $params = array_merge([
            'page' => $route['page'],
            'action' => $route['action']
        ], $additional_params);

        return add_query_arg($params, admin_url('admin.php'));
    }

    /**
     * Obtener contenido/clase para página y acción específica
     */
    public static function get_class_for_route(string $page, ?string $action = null)
    {
        if (!isset(self::$route_mappings[$page])) {
            return null;
        }

        $page_config = self::$route_mappings[$page];
        $action = $action ?: $page_config['default_action'];

        return $page_config['actions'][$action] ?? [];
    }

    public static function get_actions_for_page(string $page): ?array
    {
        if (!isset(self::$route_mappings[$page])) {
            return null;
        }

        return array_keys(self::$route_mappings[$page]['actions']);
    }

    public static function render_page(string $page, ?string $action = null)
    {
        $page_template = self::$route_mappings[$page] ?? null;
        if ($page_template === null) {
            return;
        }
        $class = self::get_class_for_route($page, $action);
        if ($action === null) {
            $action = $page_template['default_action'];
        }
        echo '<div class="wrap">';
        if (isset($page_template['header'])) {
            echo '<h1 class="wp-heading-inline">' . esc_html($page_template['header']) . '</h1>';
        }
        if (!empty($class['redirects'])) {
            foreach ($class['redirects'] as $redirect) {
                echo '<a class="page-title-action" href="' . esc_url(self::get_url_for_class($redirect['to'])) . '" class="page-title">' . esc_html($redirect['label']) . '</a>';
            }
        }
        echo '<hr class="wp-header-end">';
        if (isset($page_template['tabpane']) && $page_template['tabpane'] === true) {
            echo '<nav class="nav-tab-wrapper">';
            $panes = self::$route_mappings[$page]['actions'];
            foreach ($panes as $key => $pane) {
                if (isset($pane['is_tab']) && $pane['is_tab'] === true) {
                    echo '<a href="' . esc_url(self::get_url_for_class($pane['target'])) . '" class="nav-tab ' . ($action === $key ? 'nav-tab-active' : '') . '">' . esc_html($pane['tab_label'] ?? '') . '</a>';
                }
            }
            echo '</nav>';
        }
        if ($class) {
            echo '<div class="wrap">';
            (new $class['target']())->render();
            echo '</div>';
        } else {
            wp_die('Página no encontrada');
        }
        echo '</div>';
    }
}
