<?php
namespace CentralBooking\Profile;

use CentralBooking\Data\Constants\UserRole;
use CentralBooking\Profile\Panes\PaneCoupon;
use CentralBooking\Profile\Panes\PaneProfile;
use CentralTickets\Profile\Panes\ProfilePaneInvoice;
use CentralTickets\Profile\Panes\PaneOrders;
use CentralTickets\Profile\Panes\PanePreorders;
use CentralTickets\Profile\Panes\ProfilePaneTrip;

final class ProfileRouter
{
    public const PAGE_PROFILE = 'profile';
    public const PAGE_ORDERS = 'orders';
    public const PAGE_TICKETS = 'preorders';
    public const PAGE_COUPONS = 'coupons';
    public const PAGE_TRIPS = 'trips';
    public const PAGE_SALES = 'sales';

    private const ROUTE_MAPPINGS = [
        self::PAGE_PROFILE => [
            'label' => 'Mi Perfil',
            'roles' => [
                UserRole::CUSTOMER,
                UserRole::OPERATOR,
                UserRole::ADMINISTRATOR,
            ],
            'content' => new PaneProfile()
        ],
        self::PAGE_ORDERS => [
            'label' => 'Mis Pedidos',
            'roles' => [
                UserRole::CUSTOMER,
                UserRole::ADMINISTRATOR,
            ],
            'content' => new PaneOrders()
        ],
        self::PAGE_TICKETS => [
            'label' => 'Preordenes',
            'roles' => [
                UserRole::OPERATOR,
                UserRole::ADMINISTRATOR,
            ],
            'content' => new PanePreorders()
        ],
        self::PAGE_COUPONS => [
            'label' => 'Cupones',
            'roles' => [
                UserRole::OPERATOR,
                UserRole::ADMINISTRATOR,
            ],
            'content' => new PaneCoupon()
        ],
        self::PAGE_TRIPS => [
            'label' => 'Bitácora de Viajes',
            'roles' => [
                UserRole::OPERATOR,
                UserRole::ADMINISTRATOR,
            ],
            'content' => new ProfilePaneTrip()
        ],
        self::PAGE_SALES => [
            'label' => 'Ventas',
            'roles' => [
                UserRole::OPERATOR,
                UserRole::ADMINISTRATOR,
            ],
            'content' => new ProfilePaneInvoice()
        ],
    ];

    public static function getUrlForPage(string $page): string
    {
        $base_url = git_get_profile_page_url();
        $route = self::ROUTE_MAPPINGS[$page] ?? null;
        if ($route === null) {
            return $base_url;
        }
        $roles = $route['roles'];
        $has_permission = false;
        foreach ($roles as $role) {
            if (git_current_user_has_role($role)) {
                $has_permission = true;
                break;
            }
        }
        if (!$has_permission) {
            return $base_url;
        }

        return add_query_arg(['action' => $page], $base_url);
    }
}
