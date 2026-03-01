<?php
namespace CentralBooking\Profile;

use CentralBooking\Data\Constants\UserRole;
use CentralBooking\GUI\ComponentInterface;
use CentralBooking\Implementation\Temp\MessageAlert;
use CentralBooking\Profile\Forms\FormLogin;
use CentralBooking\Profile\Panes\PaneCoupon;
use CentralBooking\Profile\Panes\PaneProfile;
use CentralBooking\Profile\Panes\PaneInvoice;
use CentralBooking\Profile\Panes\PaneOrders;
use CentralBooking\Profile\Panes\PanePreorders;
use CentralBooking\Profile\Panes\PaneTrip;
use WP_User;

final class ProfileDashboard implements ComponentInterface
{
    private WP_User $current_user;
    private array $user_roles;
    private string $current_tab;

    public function __construct()
    {
        $this->current_user = wp_get_current_user();
        $this->user_roles = (array) $this->current_user->roles;
        $this->current_tab = $_GET['tab'] ?? 'profile';
    }

    private function loadScripts()
    {
        wp_enqueue_style(
            'operator-dashboard',
            CENTRAL_BOOKING_URL . 'assets/css/operator-dashboard.css',
            [],
            time()
        );
    }

    public function compact()
    {
        ob_start();
        $this->loadScripts();
        $this->showMessage();
        if (!is_user_logged_in()) {
            (new FormLogin)->render();
        } else {
            $this->tabPane();
        }
        return ob_get_clean();
    }

    private function tabPane()
    {
        $tabPane = git_tab_stateful_pane();

        $tabPane->addPane('Mi Perfil', new PaneProfile);

        if (git_current_user_has_role([UserRole::CUSTOMER, UserRole::ADMINISTRATOR])) {
            $tabPane->addPane('Mis Pedidos', new PaneOrders);
        }

        if (git_current_user_has_role([UserRole::OPERATOR, UserRole::ADMINISTRATOR])) {
            $tabPane->addPane('Preordenes', new PanePreorders);
        }

        if (git_current_user_has_role([UserRole::OPERATOR, UserRole::ADMINISTRATOR])) {
            $tabPane->addPane('Cupones', new PaneCoupon);
        }

        if (git_current_user_has_role([UserRole::OPERATOR, UserRole::ADMINISTRATOR])) {
            $tabPane->addPane('Bitácora de Viajes', new PaneTrip);
        }

        if (git_current_user_has_role([UserRole::OPERATOR, UserRole::ADMINISTRATOR])) {
            $tabPane->addPane('Ventas', new PaneInvoice);
        }

        $tabPane->render();
    }

    private function showMessage()
    {
        MessageAlert::getInstance(self::class)->render();
    }
}