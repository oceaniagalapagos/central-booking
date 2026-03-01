<?php
namespace CentralBooking\Admin\Setting;

use CentralBooking\GUI\DisplayerInterface;

final class SettingsSecretKey implements DisplayerInterface
{
    public function render()
    {
        ?>
        <div class="wrap">
            <div class="cb-settings-header">
                <h1>Configuración de Clave Secreta</h1>
                <p class="description">
                    Gestiona la clave secreta del sistema para garantizar la seguridad de formularios y llamadas API.
                </p>
            </div>

            <div class="cb-warning-notice">
                <div class="notice notice-warning inline">
                    <p><strong>⚠️ ADVERTENCIA IMPORTANTE:</strong></p>
                    <ul>
                        <li>Cambiar esta clave hará que TODOS los formularios actualmente cargados fallen.
                        </li>
                        <li>Todas las llamadas a la API del sistema también fallarán.
                        </li>
                        <li>Los usuarios deberán recargar todas las páginas con formularios para que funcionen correctamente.
                        </li>
                    </ul>
                </div>
            </div>

            <div class="cb-content-section">
                <h2>¿Qué es la Clave Secreta?</h2>
                <div class="cb-info-box">
                    <p>La clave secreta es un componente fundamental de seguridad que protege su sistema de Central Booking de
                        las siguientes maneras:
                    </p>

                    <div class="cb-feature-list">
                        <div class="cb-feature-item">
                            <h4>🔐 Codificación de Formularios</h4>
                            <p>Todos los formularios del sistema se codifican usando esta clave para prevenir manipulación
                                externa y garantizar que solo formularios legítimos sean procesados.
                            </p>
                        </div>

                        <div class="cb-feature-item">
                            <h4>🛡️ Protección contra Solicitudes Externas</h4>
                            <p>Evita que atacantes externos envíen datos maliciosos al sistema, ya que sin la clave correcta,
                                las solicitudes serán rechazadas automáticamente.
                            </p>
                        </div>

                        <div class="cb-feature-item">
                            <h4>🔒 Cifrado de API</h4>
                            <p>Todas las comunicaciones con la API del sistema se cifran usando esta clave, asegurando que los
                                datos sensibles estén protegidos durante el tránsito.
                            </p>
                        </div>
                    </div>
                </div>

                <h2>Configuración Actual</h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="cb_current_secret_key">Clave Secreta Actual</label>
                        </th>
                        <td>
                            <p class="description"><?= git_get_secret_key() ?></p>
                            <hr>
                            <p class="description">
                                La clave actual del sistema. Manténgala segura y confidencial.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label>Generación de Nueva Clave</label>
                        </th>
                        <td>
                            <div class="cb-key-generation-section">
                                <?php
                                $action = esc_url(add_query_arg(
                                    ['action' => 'git_settings_secret_key'],
                                    admin_url('admin-ajax.php')
                                ));
                                ?>
                                <form id="git_form_secret_key" method="post" action="<?= esc_url($action) ?>">
                                    <?php git_nonce_field(); ?>
                                    <button type="submit" class="button button-primary">
                                        Generar Nueva Clave
                                    </button>
                                </form>
                                <p class="description">
                                    <strong>Seguridad Automatizada:</strong>
                                    El sistema genera automáticamente una clave criptográficamente segura de 64 caracteres
                                    usando algoritmos aleatorios. No se permite entrada manual para mantener la máxima
                                    seguridad.
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="cb-danger-zone">
                    <h3>⚠️ Zona de Peligro</h3>
                    <p class="description">
                        Antes de cambiar la clave secreta, tenga en cuenta las siguientes consecuencias:
                    </p>
                    <div class="cb-consequences">
                        <ul>
                            <li>
                                <strong>Formularios Inactivos:</strong>
                                <span>Todos los formularios en páginas ya cargadas dejarán de funcionar
                                    inmediatamente.</span>
                            </li>
                            <li>
                                <strong>API Desconectada:</strong>
                                <span>Todas las integraciones API existentes fallarán hasta ser reconfiguradas.</span>
                            </li>
                            <li>
                                <strong>Experiencia del Usuario:</strong>
                                <span>Los usuarios deberán refrescar completamente las páginas para continuar.</span>
                            </li>
                        </ul>
                    </div>

                    <div class="cb-confirmation-section" style="display: none;">
                        <label>
                            <input type="checkbox" id="cb-confirm-change" name="cb_confirm_change" value="1" />
                            Entiendo las consecuencias y autorizo al sistema a generar una nueva clave secreta
                            automáticamente.
                        </label>
                    </div>
                </div>
                <div class="cb-best-practices">
                    <h3>📋 Mejores Prácticas</h3>
                    <ul>
                        <li>La clave secreta es generada automáticamente usando algoritmos criptográficos seguros.
                        </li>
                        <li>Cambie la clave secreta solo durante horarios de bajo tráfico.
                        </li>
                        <li>Notifique a los usuarios sobre posibles interrupciones temporales.
                        </li>
                        <li>Mantenga un respaldo de la configuración antes del cambio.
                        </li>
                        <li>La generación automática garantiza 64 caracteres con máxima entropía.
                        </li>
                        <li>Nunca comparta la clave secreta públicamente o en código fuente.
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <style>
            .cb-key-generation-section {
                border: 1px solid #2271b1;
                border-radius: 5px;
                padding: 15px;
                background: #f6f9fc;
            }

            .cb-generated-key-display {
                margin: 10px 0;
                padding: 10px;
                background: #e8f5e8;
                border: 1px solid #4caf50;
                border-radius: 3px;
            }

            .cb-key-preview {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .cb-key-preview span {
                font-family: monospace;
                font-size: 12px;
                word-break: break-all;
                flex: 1;
                background: white;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 3px;
            }

            #cb-copy-key {
                min-width: 40px;
                height: 30px;
                padding: 0;
            }

            .cb-settings-header {
                margin-bottom: 20px;
                padding-bottom: 15px;
                border-bottom: 1px solid #ddd;
            }

            .cb-warning-notice {
                margin: 20px 0;
            }

            .cb-info-box {
                background: #f9f9f9;
                border: 1px solid #ddd;
                border-radius: 5px;
                padding: 20px;
                margin: 20px 0;
            }

            .cb-feature-list {
                display: grid;
                grid-template-columns: 1fr;
                gap: 15px;
                margin-top: 15px;
            }

            .cb-feature-item {
                background: white;
                border: 1px solid #e0e0e0;
                border-radius: 5px;
                padding: 15px;
            }

            .cb-feature-item h4 {
                margin: 0 0 10px 0;
                color: #2271b1;
            }

            .cb-danger-zone {
                background: #fef7f0;
                border: 1px solid #f0a500;
                border-radius: 5px;
                padding: 20px;
                margin: 30px 0;
            }

            .cb-danger-zone h3 {
                color: #d63031;
                margin-top: 0;
            }

            .cb-consequences {
                margin: 15px 0;
            }

            .cb-consequence-item {
                display: flex;
                align-items: center;
                margin: 10px 0;
                padding: 10px;
                border-radius: 3px;
            }

            .cb-consequence-item.error {
                background: #ffeaea;
                border-left: 4px solid #dc3232;
            }

            .cb-consequence-item.warning {
                background: #fff8e1;
                border-left: 4px solid #f0a500;
            }

            .cb-consequence-item strong {
                margin-right: 10px;
                min-width: 120px;
            }

            .cb-confirmation-section {
                margin: 20px 0;
                padding: 15px;
                background: #e8f5e8;
                border: 1px solid #4caf50;
                border-radius: 5px;
            }

            .cb-best-practices {
                background: #f0f8ff;
                border: 1px solid #2196f3;
                border-radius: 5px;
                padding: 20px;
                margin: 30px 0;
            }

            .cb-best-practices h3 {
                color: #1976d2;
                margin-top: 0;
            }

            .cb-best-practices ul {
                margin: 10px 0;
            }

            .cb-best-practices li {
                margin: 8px 0;
            }

            #cb-submit-btn:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }

            @media (min-width: 768px) {
                .cb-feature-list {
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                }
            }
        </style>
        <script>
            document.getElementById('git_form_secret_key').addEventListener('submit', function (e) {
                const confirmed = confirm('¿Seguro que desea generar una nueva clave secreta? Esta acción puede afectar la seguridad y el funcionamiento del sistema.');
                if (!confirmed) {
                    e.preventDefault();
                }
            });
        </script>
        <?php
    }
}
