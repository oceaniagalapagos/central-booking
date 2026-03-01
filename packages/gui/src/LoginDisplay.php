<?php
namespace CentralBooking\GUI;

class LoginDisplay implements DisplayerInterface
{
    public function render()
    {
        wp_enqueue_style(
            'central-booking-gui-login-style',
            CENTRAL_BOOKING_URL . 'packages/gui/assets/css/login-display.css',
            [],
        );
        ?>
        <div class="git-login-form-wrapper">
            <div class="git-login-header">
                <h2>Accede a tu cuenta <b>GIT</b></h2>
            </div>

            <form method="post" action="<?= esc_url(home_url('/wp-login.php')) ?>" class="git-login-form" id="gitLoginForm">
                <?php wp_referer_field() ?>
                <div class="git-form-group">
                    <label for="username">Usuario o Email</label>
                    <input type="text" id="username" name="username" required placeholder="Ingresa tu usuario o email">
                </div>
                <div class="git-form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required placeholder="Ingresa tu contraseña">
                </div>
                <div class="git-form-group git-checkbox-group">
                    <label class="git-checkbox-label">
                        <input type="checkbox" name="rememberme" value="1" id="rememberMe">
                        <span class="git-checkmark"></span>
                        Recordarme en este dispositivo
                    </label>
                </div>
                <button type="submit" class="git-login-btn">
                    Iniciar Sesión
                </button>
            </form>
        </div>
        <?php
    }
}
