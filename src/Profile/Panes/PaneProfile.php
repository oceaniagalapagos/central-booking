<?php
namespace CentralBooking\Profile\Panes;

use CentralBooking\GUI\DisplayerInterface;
use WP_User;

final class PaneProfile implements DisplayerInterface
{
    private WP_User $current_user;

    public function __construct()
    {
        $this->current_user = wp_get_current_user();
    }

    public function render()
    {
        ?>
        <div class="git-profile-container">
            <div class="git-profile-section">
                <div class="git-profile-card">
                    <div class="git-profile-card-body">
                        <div class="git-profile-header">
                            <div class="git-profile-info">
                                <div class="git-profile-user">
                                    <div class="git-avatar-wrapper">
                                        <?= get_avatar($this->current_user->ID, 80, '', '', [
                                            'class' => 'git-avatar'
                                        ]) ?>
                                        <span class="git-status-indicator git-status-online">
                                            <span class="git-status-dot"></span>
                                        </span>
                                    </div>
                                    <div class="git-user-details">
                                        <h3 class="git-user-name">
                                            <?= esc_html($this->get_full_name()) ?>
                                        </h3>
                                        <div class="git-user-badges">
                                            <span class="git-badge git-badge-primary">
                                                <?= esc_html($this->get_role_label()) ?>
                                            </span>
                                            <span class="git-badge git-badge-secondary">
                                                Miembro desde <?= esc_html($this->get_member_since()) ?>
                                            </span>
                                        </div>
                                        <div class="git-user-email">
                                            <?= esc_html($this->current_user->user_email) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="git-profile-actions">
                                <a href="<?= wp_logout_url(home_url()) ?>" class="git-btn git-btn-danger">
                                    Cerrar Sesión
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php
    }

    private function get_full_name(): string
    {
        $first = $this->current_user->first_name;
        $last = $this->current_user->last_name;

        if (!empty($first) || !empty($last)) {
            return trim($first . ' ' . $last);
        }

        return $this->current_user->display_name ?: $this->current_user->user_login;
    }

    private function get_role_label(): string
    {
        $roles = $this->current_user->roles;

        if (empty($roles)) {
            return 'Usuario';
        }

        $role_labels = [
            'administrator' => 'Administrador',
            'operator' => 'Operador',
            'customer' => 'Cliente',
            'subscriber' => 'Suscriptor'
        ];

        return $role_labels[$roles[0]] ?? ucfirst($roles[0]);
    }

    private function get_member_since(): string
    {
        return date('F Y', strtotime($this->current_user->user_registered));
    }
}