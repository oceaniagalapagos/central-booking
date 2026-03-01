<?php
namespace CentralBooking\Profile\Panes;

use CentralBooking\GUI\DisplayerInterface;

final class PaneDefault implements DisplayerInterface
{
    public function render()
    {
        ?>
        <div class="git-profile-section">
            <div class="git-profile-card">
                <div class="git-profile-card-body">
                    <!-- Contenido principal del panel por defecto -->
                    <div class="git-plugin-info">
                        <div class="git-plugin-icon">
                            <i class="dashicons dashicons-info"></i>
                        </div>

                        <h3 class="git-plugin-title">Opción no disponible</h3>

                        <div class="git-plugin-description">
                            <p>Lo sentimos, la opción que has seleccionado no está disponible en este momento o no se ha
                                implementado aún.</p>
                        </div>

                        <div class="notice notice-info">
                            <p><strong>¿Necesitas ayuda?</strong> Si crees que esto es un error, por favor contacta al
                                administrador del sistema.</p>
                        </div>

                        <div class="git-plugin-actions">
                            <a href="javascript:history.back()" class="git-btn git-btn-primary">
                                <i class="dashicons dashicons-arrow-left-alt2"></i>
                                Volver atrás
                            </a>

                            <a href="<?php echo admin_url('admin.php?page=central-booking'); ?>"
                                class="git-btn git-btn-secondary">
                                <i class="dashicons dashicons-admin-home"></i>
                                Ir al panel principal
                            </a>
                        </div>

                        <div class="git-plugin-notice">
                            <strong>Nota:</strong> Si continúas experimentando problemas, verifica que tengas los permisos
                            adecuados o contacta al soporte técnico.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .git-plugin-icon .dashicons {
                font-size: 32px;
                width: auto;
                height: auto;
            }

            .git-btn .dashicons {
                margin-right: 8px;
                font-size: 16px;
                text-decoration: none;
            }

            .git-plugin-actions .git-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                margin: 5px;
            }
        </style>
        <?php
    }
}