<?php

use CentralBooking\Data\Constants\TypeOperation;
use CentralBooking\Data\Constants\TypeWay;
use CentralBooking\Data\Zone;

/**
 * Clase para productos de tipo operador en el sistema de reservas centralizadas
 *
 * Esta clase extiende WC_Product para manejar productos específicos de operadores
 * de transporte/tours que incluyen capacidades, precios por tipo de pasajero,
 * zonas de origen y destino, y configuraciones específicas de operación.
 *
 * @since 1.0.0
 * @package CentralBooking
 * @subpackage Products
 */
class WC_Product_Operator extends WC_Product
{
    /**
     * Constructor de la clase WC_Product_Operator
     *
     * @since 1.0.0
     * @param WC_Product|int $product Instancia del producto o ID del producto
     */
    public function __construct($product)
    {
        parent::__construct($product);
        $this->product_type = 'operator';
    }

    /**
     * Obtiene el precio para niños
     *
     * @since 1.0.0
     * @return float|int Precio para niños, 0 si no está definido
     */
    public function get_price_kid()
    {
        return $this->get_meta('git_price_kid') ?? 0;
    }

    /**
     * Establece el precio para niños
     *
     * @since 1.0.0
     * @param float|int $price Precio para niños
     * @return void
     */
    public function set_price_kid($price)
    {
        $this->update_meta_data('git_price_kid', $price);
    }

    /**
     * Obtiene el precio RPM (Residente Península de México)
     *
     * @since 1.0.0
     * @return float|int Precio para residentes de la península de México, 0 si no está definido
     */
    public function get_price_rpm()
    {
        return $this->get_meta('git_price_rpm') ?? 0;
    }

    /**
     * Establece el precio RPM (Residente Península de México)
     *
     * @since 1.0.0
     * @param float|int $price Precio para residentes de la península de México
     * @return void
     */
    public function set_price_rpm($price)
    {
        $this->update_meta_data('git_price_rpm', $price);
    }

    /**
     * Obtiene el precio estándar
     *
     * @since 1.0.0
     * @return float|int Precio estándar, 0 si no está definido
     */
    public function get_price_standard()
    {
        return $this->get_meta('git_price_standard') ?? 0;
    }

    /**
     * Establece el precio estándar
     *
     * @since 1.0.0
     * @param float|int $price Precio estándar
     * @return void
     */
    public function set_price_standard($price)
    {
        $this->update_meta_data('git_price_standard', $price);
    }

    /**
     * Obtiene el precio extra
     *
     * @since 1.0.0
     * @return float|int Precio extra, 0 si no está definido
     */
    public function get_price_extra()
    {
        return $this->get_meta('git_price_extra') ?? 0;
    }

    /**
     * Establece el precio extra
     *
     * @since 1.0.0
     * @param float|int $price Precio extra
     * @return void
     */
    public function set_price_extra($price)
    {
        $this->update_meta_data('git_price_extra', $price);
    }

    /**
     * Obtiene el precio flexible
     *
     * @since 1.0.0
     * @return float|int Precio flexible, 0 si no está definido
     */
    public function get_price_flexible()
    {
        return $this->get_meta('git_price_flexible') ?? 0;
    }

    /**
     * Establece el precio flexible
     *
     * @since 1.0.0
     * @param float|int $price Precio flexible
     * @return void
     */
    public function set_price_flexible($price)
    {
        $this->update_meta_data('git_price_flexible', $price);
    }

    /**
     * Establece si el producto es intercambiable
     *
     * @since 1.0.0
     * @param bool $switchable True si es intercambiable, false en caso contrario
     * @return void
     */
    public function set_switchable(bool $switchable)
    {
        $this->update_meta_data('git_is_switchable', $switchable ? 'true' : 'false');
    }

    /**
     * Verifica si el producto es intercambiable
     *
     * @since 1.0.0
     * @return bool True si es intercambiable, false en caso contrario
     */
    public function is_switchable()
    {
        return $this->get_meta('git_is_switchable') === 'true';
    }

    /**
     * Establece la capacidad de personas
     *
     * @since 1.0.0
     * @param int $capacity Capacidad máxima de personas (debe ser >= 0)
     * @return void
     */
    public function set_capacity_people(int $capacity)
    {
        if ($capacity < 0) {
            return;
        }
        $this->update_meta_data('git_capacity_people', $capacity);
    }

    /**
     * Obtiene la capacidad de personas
     *
     * @since 1.0.0
     * @return int Capacidad máxima de personas, 0 si no está definido
     */
    public function get_capacity_people()
    {
        return (int) ($this->get_meta('git_capacity_people') ?? 0);
    }

    /**
     * Establece la capacidad extra
     *
     * @since 1.0.0
     * @param int $capacity Capacidad extra (debe ser >= 0)
     * @return void
     */
    public function set_capacity_extra(int $capacity)
    {
        if ($capacity < 0) {
            return;
        }
        $this->update_meta_data('git_capacity_extra', $capacity);
    }

    /**
     * Obtiene la capacidad extra
     *
     * @since 1.0.0
     * @return int Capacidad extra, 0 si no está definido
     */
    public function get_capacity_extra()
    {
        return (int) ($this->get_meta('git_capacity_extra') ?? 0);
    }

    /**
     * Verifica si el producto puede ser comprado
     *
     * @since 1.0.0
     * @return bool True si puede ser comprado, false en caso contrario
     */
    public function is_purchasable()
    {
        $purchasable = $this->get_meta('git_is_purchasable');
        if ($purchasable === null) {
            return parent::is_purchasable();
        }
        return $purchasable === 'true' && parent::is_purchasable();
    }

    /**
     * Establece si el producto puede ser comprado
     *
     * @since 1.0.0
     * @param bool $purchasable True si puede ser comprado, false en caso contrario
     * @return void
     */
    public function set_purchasable(bool $purchasable)
    {
        $this->update_meta_data('git_is_purchasable', $purchasable ? 'true' : 'false');
    }

    /**
     * Obtiene el tipo de operación
     *
     * @since 1.0.0
     * @return TypeOperation|null Tipo de operación o null si no está definido
     */
    public function get_type_operation()
    {
        $slug = $this->get_meta('git_type_operation');
        if ($slug === null) {
            return null;
        }
        return TypeOperation::fromSlug((string) $slug);
    }

    /**
     * Establece el tipo de operación
     *
     * @since 1.0.0
     * @param TypeOperation $type Tipo de operación
     * @return void
     */
    public function set_type_operation(TypeOperation $type)
    {
        $this->update_meta_data('git_type_operation', $type->slug());
    }

    /**
     * Obtiene el tipo de ruta
     *
     * @since 1.0.0
     * @return TypeWay|null Tipo de ruta o null si no está definido
     */
    public function get_type_way()
    {
        $slug = $this->get_meta('git_type_way');
        if ($slug === null) {
            return null;
        }
        return TypeWay::fromSlug((string) $slug);
    }

    /**
     * Establece el tipo de ruta
     *
     * @since 1.0.0
     * @param TypeWay $type Tipo de ruta
     * @return void
     */
    public function set_type_way(TypeWay $type)
    {
        $this->update_meta_data('git_type_way', $type->slug());
    }

    /**
     * Verifica si tiene carrusel de transporte
     *
     * @since 1.0.0
     * @return bool True si tiene carrusel de transporte, false en caso contrario
     */
    public function is_carousel_transport()
    {
        return $this->get_meta('git_has_carousel_transport') === 'true';
    }

    /**
     * Establece si tiene carrusel de transporte
     *
     * @since 1.0.0
     * @param bool $has_carousel True si tiene carrusel de transporte, false en caso contrario
     * @return void
     */
    public function set_carousel_transport(bool $has_carousel)
    {
        $this->update_meta_data('git_has_carousel_transport', $has_carousel ? 'true' : 'false');
    }

    /**
     * Verifica si el transporte se divide por alias
     *
     * @since 1.0.0
     * @return bool True si el transporte se divide por alias, false en caso contrario
     */
    public function is_split_transport_by_alias()
    {
        return $this->get_meta('git_split_transport_by_alias') === 'true';
    }

    /**
     * Establece si el transporte se divide por alias
     *
     * @since 1.0.0
     * @param bool $split True si el transporte se divide por alias, false en caso contrario
     * @return void
     */
    public function set_split_transport_by_alias(bool $split)
    {
        $this->update_meta_data('git_split_transport_by_alias', $split ? 'true' : 'false');
    }

    /**
     * Obtiene la zona de origen
     *
     * @since 1.0.0
     * @return Zone Zona de origen o una zona nueva si no está definida
     */
    public function get_zone_origin()
    {
        $id = (int) ($this->get_meta('git_zone_origin') ?? 0);
        return git_zone_by_id($id) ?? git_zone_create();
    }

    /**
     * Establece la zona de origen
     *
     * @since 1.0.0
     * @param Zone|int $zone Zona de origen o ID de la zona
     * @return void
     */
    public function set_zone_origin(Zone|int $zone)
    {
        $id = is_object($zone) ? $zone->id : $zone;
        $this->update_meta_data('git_zone_origin', $id);
    }

    /**
     * Obtiene la zona de destino
     *
     * @since 1.0.0
     * @return Zone Zona de destino o una zona nueva si no está definida
     */
    public function get_zone_destiny()
    {
        $id = (int) ($this->get_meta('git_zone_destiny') ?? 0);
        return git_zone_by_id($id) ?? git_zone_create();
    }

    /**
     * Establece la zona de destino
     *
     * @since 1.0.0
     * @param Zone|int $zone Zona de destino o ID de la zona
     * @return void
     */
    public function set_zone_destiny(Zone|int $zone)
    {
        $id = is_object($zone) ? $zone->id : $zone;
        $this->update_meta_data('git_zone_destiny', $id);
    }

    /**
     * Obtiene el HTML del precio formateado
     *
     * Calcula el rango de precios basado en los precios de niño, RPM y estándar,
     * y devuelve un string formateado con el rango de precios.
     *
     * @since 1.0.0
     * @param string $deprecated Parámetro deprecado para compatibilidad
     * @return string HTML del precio formateado como "min - max"
     */
    public function get_price_html($deprecated = '')
    {
        if ($this->is_purchasable() === false) {
            return 'No disponible';
        }
        $prices = [(float) $this->get_price_kid(), (float) $this->get_price_rpm(), (float) $this->get_price_standard()];
        $max = max(array_values($prices));
        $min = min(array_values($prices));
        return git_currency_format($min, false) . ' - ' . git_currency_format($max, false);
    }
}
