<?php
namespace CentralBooking\WooCommerce;

use CentralBooking\Data\Constants\TypeOperation;
use CentralBooking\Data\Constants\TypeWay;
use WC_Product_Operator;

final class ProductForm
{
    private function __construct()
    {
    }

    public static function get_tabs()
    {
        return [
            'general_operator' => [
                'label' => 'General',
                'target' => 'git_general_product_data',
                'class' => ['show_if_operator'],
                'priority' => 25,
            ],
            'pricing' => [
                'label' => 'Rutas y Capacidad',
                'target' => 'git_routes_product_data',
                'class' => ['show_if_operator'],
                'priority' => 26,
            ],
            'inventory' => [
                'label' => 'Precios y Tarifas',
                'target' => 'git_pricing_product_data',
                'class' => ['show_if_operator'],
                'priority' => 27,
            ],
        ];
    }

    public static function get_general_panel()
    {
        $types_way = [];
        $types_operation = [];

        foreach (TypeOperation::cases() as $type) {
            if ($type === TypeOperation::NONE || $type === TypeOperation::LAND)
                continue;
            $types_operation[$type->slug()] = $type->label();
        }

        foreach (TypeWay::cases() as $type) {
            $types_way[$type->slug()] = $type->label();
        }
        ?>
        <div id="git_general_product_data" class="panel woocommerce_options_panel">
            <div class="options_group">
                <?php
                global $post;
                $product = wc_get_product($post->ID);

                woocommerce_wp_checkbox([
                    'id' => 'is_purchasable',
                    'value' => $product instanceof WC_Product_Operator ? ($product->is_purchasable() ? 'yes' : 'no') : 'no',
                    'label' => 'Puede reservarse',
                    'description' => 'Permite que el producto esté disponible para reservas en la tienda'
                ]);

                woocommerce_wp_checkbox([
                    'id' => 'has_switch_route',
                    'value' => $product instanceof WC_Product_Operator ? ($product->is_switchable() ? 'yes' : 'no') : 'no',
                    'label' => 'Habilitar switch de ruta',
                    'description' => 'Permite cambiar el sentido de la ruta durante la reserva'
                ]);

                woocommerce_wp_checkbox([
                    'id' => 'split_transport_by_alias',
                    'value' => $product instanceof WC_Product_Operator ? ($product->is_split_transport_by_alias() ? 'yes' : 'no') : 'no',
                    'label' => 'Separar transporte por alias',
                    'description' => 'Crea una opción de transporte por cada alias definido en el mismo'
                ]);

                woocommerce_wp_checkbox([
                    'id' => 'has_carousel_transport',
                    'value' => $product instanceof WC_Product_Operator ? ($product->is_carousel_transport() ? 'yes' : 'no') : 'no',
                    'label' => 'Habilitar carrusel de transportes',
                    'description' => 'Permite mostrar los transportes en un carrusel'
                ]);

                woocommerce_wp_select([
                    'id' => 'type_operation',
                    'value' => $product instanceof WC_Product_Operator ? ($product->get_type_operation()?->slug() ?? '') : '',
                    'label' => 'Tipo de operación',
                    'options' => $types_operation,
                    'description' => 'Selecciona el tipo de medio donde ocurre la operación (mar, tierra, aire, etc.)'
                ]);

                woocommerce_wp_select([
                    'id' => 'type_way',
                    'value' => $product instanceof WC_Product_Operator ? ($product->get_type_way()?->slug() ?? '') : '',
                    'label' => 'Tipo de trayecto',
                    'options' => $types_way,
                    'description' => 'Define si es ida, vuelta o ida y vuelta'
                ]);
                ?>
            </div>
        </div>
        <?php
    }

    public static function get_pricing_panel()
    {
        $zones = [];

        foreach (git_zones() as $zone) {
            $zones[$zone->id] = $zone->name;
        }

        ?>
        <div id="git_routes_product_data" class="panel woocommerce_options_panel">
            <?php
            global $post;
            $product = wc_get_product($post->ID);

            woocommerce_wp_select([
                'id' => 'zone_origin',
                'value' => $product instanceof WC_Product_Operator ? ($product->get_zone_origin()?->id ?? 0) : 0,
                'label' => 'Zona de origen',
                'options' => $zones,
                'description' => 'Punto de partida del transporte'
            ]);

            woocommerce_wp_select([
                'id' => 'zone_destiny',
                'value' => $product instanceof WC_Product_Operator ? ($product->get_zone_destiny()?->id ?? 0) : 0,
                'label' => 'Zona de destino',
                'options' => $zones,
                'description' => 'Punto de llegada del transporte'
            ]);

            woocommerce_wp_text_input([
                'id' => 'capacity_people',
                'value' => $product instanceof WC_Product_Operator ? $product->get_capacity_people() : '',
                'label' => 'Máximo de personas por operación',
                'placeholder' => '0',
                'type' => 'number',
                'desc_tip' => true,
                'description' => 'Número máximo de pasajeros permitidos'
            ]);

            woocommerce_wp_text_input([
                'id' => 'capacity_extra',
                'value' => $product instanceof WC_Product_Operator ? $product->get_capacity_extra() : '',
                'label' => 'Máximo de equipaje extra',
                'placeholder' => '0',
                'type' => 'number',
                'desc_tip' => true,
                'description' => 'Cantidad máxima de equipaje adicional permitido'
            ]);
            ?>
        </div>
        <?php
    }

    public static function get_inventory_panel()
    {
        ?>
        <div id="git_pricing_product_data" class="panel woocommerce_options_panel">
            <div class="options_group">
                <?php
                // Obtener el producto actual
                global $post;
                $product = wc_get_product($post->ID);

                woocommerce_wp_text_input([
                    'id' => 'price_standard',
                    'value' => $product instanceof WC_Product_Operator ? $product->get_price_standard() : '',
                    'label' => 'Precio estándar',
                    'placeholder' => '$0.00',
                    'type' => 'number',
                    'custom_attributes' => ['step' => '0.01'],
                    'desc_tip' => true,
                    'description' => 'Precio regular para adultos'
                ]);

                woocommerce_wp_text_input([
                    'id' => 'price_kid',
                    'value' => $product instanceof WC_Product_Operator ? $product->get_price_kid() : '',
                    'label' => 'Precio para niños',
                    'placeholder' => '$0.00',
                    'type' => 'number',
                    'custom_attributes' => ['step' => '0.01'],
                    'desc_tip' => true,
                    'description' => 'Precio especial para menores de edad'
                ]);
                ?>
            </div>

            <div class="options_group">
                <?php
                woocommerce_wp_text_input([
                    'id' => 'price_rpm',
                    'value' => $product instanceof WC_Product_Operator ? $product->get_price_rpm() : '',
                    'label' => 'Precio para usuarios RPM',
                    'placeholder' => '$0.00',
                    'type' => 'number',
                    'custom_attributes' => ['step' => '0.01'],
                    'desc_tip' => true,
                    'description' => 'Precio con descuento para miembros RPM'
                ]);

                woocommerce_wp_text_input([
                    'id' => 'price_flexible',
                    'value' => $product instanceof WC_Product_Operator ? $product->get_price_flexible() : '',
                    'label' => 'Precio flexible',
                    'placeholder' => '$0.00',
                    'type' => 'number',
                    'custom_attributes' => ['step' => '0.01'],
                    'desc_tip' => true,
                    'description' => 'Precio para reservas con cambios permitidos'
                ]);
                ?>
            </div>

            <div class="options_group">
                <?php
                woocommerce_wp_text_input([
                    'id' => 'price_extra',
                    'value' => $product instanceof WC_Product_Operator ? $product->get_price_extra() : '',
                    'label' => 'Precio cargo extra',
                    'placeholder' => '$0.00',
                    'type' => 'number',
                    'custom_attributes' => ['step' => '0.01'],
                    'desc_tip' => true,
                    'description' => 'Costo adicional por equipaje extra o servicios especiales'
                ]);
                ?>
            </div>
        </div>
        <?php
    }

    public static function process_form(int $post_id)
    {
        $product = wc_get_product($post_id);
        if ($product instanceof WC_Product_Operator) {

            // Limpiar y validar datos antes de guardar
            $price_kid = floatval($_POST['price_kid'] ?? 0);
            $price_rpm = floatval($_POST['price_rpm'] ?? 0);
            $price_extra = floatval($_POST['price_extra'] ?? 0);
            $capacity_extra = intval($_POST['capacity_extra'] ?? 0);
            $capacity_people = intval($_POST['capacity_people'] ?? 0);
            $price_flexible = floatval($_POST['price_flexible'] ?? 0);
            $price_standard = floatval($_POST['price_standard'] ?? 0);
            $type_way = sanitize_text_field($_POST['type_way'] ?? '');
            $type_operation = sanitize_text_field($_POST['type_operation'] ?? '');

            // Configurar precios del producto
            $product->set_sale_price(0);
            $product->set_price($price_standard);

            // Guardar precios específicos
            $product->set_price_kid($price_kid);
            $product->set_price_rpm($price_rpm);
            $product->set_price_extra($price_extra);
            $product->set_price_flexible($price_flexible);
            $product->set_price_standard($price_standard);
            $product->set_capacity_extra($capacity_extra);
            $product->set_capacity_people($capacity_people);

            $product->set_type_way(TypeWay::fromSlug($type_way) ?? TypeWay::NONE);
            $product->set_type_operation(TypeOperation::fromSlug($type_operation) ?? TypeOperation::NONE);

            // Guardar checkboxes - usar métodos personalizados
            $product->set_purchasable(isset($_POST['is_purchasable']));
            $product->set_switchable(isset($_POST['has_switch_route']));
            $product->set_carousel_transport(isset($_POST['has_carousel_transport']));
            $product->set_split_transport_by_alias(isset($_POST['split_transport_by_alias']));

            // Guardar metadatos - CORREGIDO: usar valores reales en lugar de isset()
            $product->set_zone_origin((int) ($_POST['zone_origin'] ?? 0));
            $product->set_zone_destiny((int) ($_POST['zone_destiny'] ?? 0));

            // Guardar cambios en la base de datos
            $product->save();

            // Limpiar cache de WooCommerce
            wc_delete_product_transients($post_id);
        }
    }
}
