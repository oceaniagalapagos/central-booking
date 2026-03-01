<?php
namespace CentralBooking\Profile\Forms;

use CentralBooking\GUI\DisplayerInterface;

final class FormLogin implements DisplayerInterface
{
    public function render()
    {
        $action = add_query_arg(
            ['action' => 'git_login'],
            admin_url('admin-ajax.php')
        );
        ?>
        <div class="git-login-form-wrapper">
            <div class="git-login-header">
                <h2>Accede a tu cuenta <b>GIT</b></h2>
            </div>
            <form method="post" action="<?= esc_url($action) ?>" class="git-form">
                <?php git_nonce_field() ?>
                <?php wp_referer_field() ?>
                <div class="git-form-group">
                    <label for="username">Usuario</label>
                    <input type="text" id="username" name="username" required autocomplete="username">
                </div>
                <div class="git-form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
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
