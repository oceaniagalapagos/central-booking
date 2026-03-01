<?php
namespace CentralBooking\Admin\Setting;

use CentralBooking\Admin\AdminRouter;
use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\REST\RegisterRoute;

final class SettingsAPIDoc implements DisplayerInterface
{
    public function render()
    {
        $bearer_token = git_api_key();
        $url_base = home_url('/wp-json/' . RegisterRoute::prefix);
        ?>
        <h2>Documentación API RESTful</h2>
        <p>
            Esta documentación describe los endpoints disponibles en la API RESTful del sistema de reservas. La API permite
            interactuar con las entidades principales como zonas, ubicaciones, servicios, rutas, transportes, tickets y
            pasajeros. Además de exponer otros servicios relacionados con la gestión de reservas.
        </p>
        
        <div class="api-security-info"
            style="background-color: #e7f3ff; border: 1px solid #b8daff; border-radius: 5px; padding: 15px; margin: 20px 0;">
            <h3>Seguridad de la API</h3>
            <p><strong>Endpoints Públicos (GET):</strong> Las solicitudes GET son completamente abiertas y no requieren
                autenticación. Puedes acceder a ellos directamente sin ningún token.</p>
            <p><strong>Endpoints Protegidos (POST/PUT):</strong> Las solicitudes POST y PUT están protegidas y requieren
                autenticación mediante Bearer Token.</p>
            <table>
                <tr>
                    <td>
                        URL Base:
                    </td>
                    <td>
                        <code><?= $url_base; ?></code>
                    </td>
                </tr>
                <tr>
                    <td>
                        Bearer Token:
                    </td>
                    <td>
                        <code><?= $bearer_token ?></code>
                    </td>
                </tr>
            </table>
            <p>
                <i><b>Nota <small>1</small>:</b> El token debe incluirse en el encabezado de autorización de la petición
                    HTTP.</i>
                <br>
                <i><b>Nota <small>2</small>:</b> El token se genera automáticamente teniendo en cuenta la clave secreta del
                    sistema. Si desea cambiarlo, lea la <a
                        href="<?= esc_url(AdminRouter::get_url_for_class(SettingsSecretKey::class)) ?>"
                        style="color: #0073aa; text-decoration: underline;">documentación de la clave secreta</a>.</i>
            </p>
        </div>

        <h2>Lista de endpoints disponibles</h2>
        <ul>
            <li><a href="#api-doc-zone">Endpoints de zona.</a></li>
            <li><a href="#api-doc-location">Endpoints de ubicación.</a></li>
            <li><a href="#api-doc-service">Endpoints de servicio.</a></li>
            <li><a href="#api-doc-route">Endpoints de ruta.</a></li>
            <li><a href="#api-doc-transport">Endpoints de transporte.</a></li>
            <li><a href="#api-doc-ticket">Endpoints de ticket.</a></li>
            <li><a href="#api-doc-passenger">Endpoints de pasajero.</a></li>
        </ul>
        <div id="api-doc-zone">
            <h2>Endpoints de zonas</h2>
            <div class="endpoint-section">
                <div class="endpoint-info">
                    <span class="http-method get">GET</span>
                    <code>/zones</code>
                </div>
                <p>Obtiene las zonas registradas en el sistema con opciones de filtrado y paginación.</p>
                <h4>Parámetros de Consulta:</h4>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Parámetro</th>
                            <th>Tipo</th>
                            <th>Descripción</th>
                            <th>Ejemplo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>limit</strong></td>
                            <td>integer</td>
                            <td>Número de registros a devolver (por defecto no hay límite)</td>
                            <td>?limit=10</td>
                        </tr>
                        <tr>
                            <td><strong>offset</strong></td>
                            <td>integer</td>
                            <td>Número de registros a omitir (por defecto: 0)</td>
                            <td>?offset=20</td>
                        </tr>
                        <tr>
                            <td><strong>order</strong></td>
                            <td>string</td>
                            <td>Orden de clasificación: 'asc' o 'desc' (por defecto: 'asc')</td>
                            <td>?order=desc</td>
                        </tr>
                        <tr>
                            <td><strong>order_by</strong></td>
                            <td>string</td>
                            <td>Campo para ordenar: 'id', 'name' (por defecto: 'id')</td>
                            <td>?order_by=name</td>
                        </tr>
                    </tbody>
                </table>
                <h4 id="table-filters-zones">Filtros disponibles:</h4>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Filtro</th>
                            <th>Clave</th>
                            <th>Tipo</th>
                            <th>Ejemplo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>id</strong></td>
                            <td>integer</td>
                            <td>ID de la zona</td>
                            <td>?id=1</td>
                        </tr>
                        <tr>
                            <td><strong>name</strong></td>
                            <td>string</td>
                            <td>Nombre de la zona</td>
                            <td>?name=Isabela</td>
                        </tr>
                    </tbody>
                </table>
                <h4>Petición básica:</h4>
                <pre><code>GET <?= "{$url_base}zones"; ?></code></pre>
                <h4>Con filtros y paginación:</h4>
                <pre><code>GET <?= "{$url_base}zones?limit=10&order_by=name&order=asc"; ?></code></pre>
                <h4>Con múltiples parámetros:</h4>
                <pre><code>GET <?= "{$url_base}zones?limit=5&offset=10&order_by=id&order=desc"; ?></code></pre>
                <h4>Con filtros:</h4>
                <pre><code>GET <?= "{$url_base}zones?id=1&name=Isabela"; ?></code></pre>
                <hr>
                <p>
                    <strong>Ejemplo de Respuesta:</strong>
                </p>
                <div class="api-code-block">
                    <pre><?= json_encode($this->sample_request_get_zones, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                </div>
            </div>

            <!-- GET Zone by ID -->
            <div class="endpoint-section">
                <div class="endpoint-info">
                    <span class="http-method get">GET</span>
                    <code>/zones/{id}</code>
                </div>
                <p>Obtiene una zona específica por su ID.</p>

                <h4>Ejemplo de Petición:</h4>
                <pre><code>GET <?= "{$url_base}zones/1"; ?></code></pre>

                <h4>Ejemplo de Respuesta:</h4>
                <div class="api-code-block">
                    <pre><?= json_encode($this->sample_request_get_zone, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                </div>
            </div>

            <!-- POST Create Zone -->
            <div class="endpoint-section">
                <div class="endpoint-info">
                    <span class="http-method post">POST</span>
                    <code>/zones</code>
                    <span class="auth-required">🔒 Token Requerido</span>
                </div>
                <p><strong>Descripción:</strong> Crear una nueva zona</p>

                <h4>Encabezados:</h4>
                <table>
                    <tr>
                        <th>Authorization:</th>
                        <td>Bearer <?= $bearer_token ?></td>
                    </tr>
                    <tr>
                        <th>Content-Type:</th>
                        <td>application/json</td>
                    </tr>
                </table>

                <h4>Cuerpo de la Petición:</h4>
                <div class="api-code-block">
                    <pre><?= json_encode($this->sample_request_post_zone, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                </div>
                <h4>Ejemplo de Respuesta (201 Creado):</h4>
                <div class="api-code-block">
                    <pre><?= json_encode(
                        $this->sample_request_post_zone,
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                    ) ?></pre>
                </div>
                <h4>Respuesta de Error (401 No Autorizado):</h4>
                <h4>Respuesta de Error (422 Error de Validación):</h4>
            </div>

            <!-- PUT Update Zone -->
            <div class="endpoint-section">
                <div class="endpoint-info">
                    <span class="http-method put">PUT</span>
                    <code>/zones/{id}</code>
                    <span class="auth-required">🔒 Token Requerido</span>
                </div>
                <p><strong>Descripción:</strong> Actualizar una zona existente</p>

                <h4>Parámetros:</h4>
                <ul>
                    <li><strong>id</strong> (entero, requerido) - ID de la zona a actualizar</li>
                </ul>

                <h4>Encabezados:</h4>
                <ul>
                    <li><strong>Authorization:</strong> Bearer <?= $bearer_token ?></li>
                    <li><strong>Content-Type:</strong> application/json</li>
                </ul>

                <h4>Cuerpo de la Petición:</h4>
                <div class="api-code-block">
                    <pre><?= json_encode($this->sample_request_post_zone, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                </div>
                <h4>Ejemplo de Petición:</h4>
                <h4>Ejemplo de Respuesta (200 OK):</h4>
                <h4>Respuesta de Error (404 No Encontrado):</h4>
            </div>
        </div>

        <style>
            .api-doc-container {
                max-width: 1200px;
            }

            .endpoint-section {
                margin-bottom: 40px;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 5px;
            }

            .endpoint-info {
                margin: 10px 0;
            }

            .http-method {
                padding: 4px 8px;
                color: white;
                font-weight: bold;
                border-radius: 3px;
                margin-right: 10px;
            }

            .http-method.get {
                background-color: #28a745;
            }

            .http-method.post {
                background-color: #007bff;
            }

            .http-method.put {
                background-color: #ffc107;
                color: #212529;
            }

            .auth-required {
                background-color: #dc3545;
                color: white;
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 12px;
                margin-left: 10px;
            }

            /* Para endpoints y rutas */
            .api-endpoint {
                font-family: 'Courier New', monospace;
                background-color: #f6f8fa;
                padding: 2px 6px;
                border-radius: 4px;
                border: 1px solid #d1d9e0;
                font-size: 13px;
                color: #0366d6;
                font-weight: 500;
            }

            /* Para bloques de código JSON y requests */
            .api-code-block {
                font-family: 'Courier New', monospace;
                background-color: #f8f9fa;
                border: 1px solid #e9ecef;
                border-radius: 4px;
                padding: 15px;
                overflow-x: auto;
                white-space: pre;
                font-size: 13px;
                line-height: 1.4;
                color: #24292f;
                margin: 10px 0;
            }

            /* Para URLs base */
            .api-base-url {
                font-family: 'Courier New', monospace;
                background-color: #fff5b4;
                padding: 4px 8px;
                border-radius: 4px;
                border: 1px solid #d4b106;
                font-size: 14px;
                color: #24292f;
                font-weight: 600;
            }

            /* Para respuestas exitosas */

            .api-code-block {
                background-color: #e6ffed;
                border-color: #a2f5bf;
                padding: 10px;
            }

            .api-code-block pre {
                background-color: none;
            }
        </style>
        <?php
    }

    private array $sample_request_get_zones = [
        'data' => [
            [
                'id' => 1,
                'name' => 'Zona A',
                'description' => 'Descripción de la Zona A'
            ],
            [
                'id' => 2,
                'name' => 'Zona B',
                'description' => 'Descripción de la Zona B'
            ]
        ],
        'pagination' => [
            'total' => 50,
            'count' => 2,
            'per_page' => 10,
            'current_page' => 1,
            'total_pages' => 5
        ]
    ];

    private array $sample_request_get_zone = [
        'id' => 1,
        'name' => 'Zona A',
        'description' => 'Descripción de la Zona A'
    ];

    private array $sample_request_post_zone = [
        'name' => 'Loja',
        'locations_id' => [1, 2, 3]
    ];
}