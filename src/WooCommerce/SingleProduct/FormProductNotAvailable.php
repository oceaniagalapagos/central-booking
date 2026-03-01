<?php
namespace CentralBooking\WooCommerce\SingleProduct;

use CentralBooking\GUI\DisplayerInterface;

class FormProductNotAvailable implements DisplayerInterface
{
    public function render()
    {
        ?>
        <div class="product-not-available-form">
            <div class="not-available-message">
                <div class="message-icon">
                    <span class="dashicons dashicons-warning"></span>
                </div>

                <div class="message-content">
                    <h3><?php esc_html_e('Producto no disponible', 'central-tickets'); ?></h3>
                    <p><?php esc_html_e('Este producto no está disponible actualmente o ha sido descontinuado.', 'central-tickets'); ?>
                    </p>

                    <div class="suggested-actions">
                        <h4><?php esc_html_e('¿Qué puedes hacer?', 'central-tickets'); ?></h4>

                        <div class="action-buttons">
                            <a href="<?= esc_url($this->get_shop_url()) ?>" class="button button-primary shop-button">
                                <span class="dashicons dashicons-store"></span>
                                <?php esc_html_e('Explorar otros productos', 'central-tickets'); ?>
                            </a>

                            <?php if ($this->is_woocommerce_active()): ?>
                                <a href="<?= esc_url($this->get_product_categories_url()) ?>"
                                    class="button button-secondary categories-button">
                                    <span class="dashicons dashicons-category"></span>
                                    <?php esc_html_e('Ver categorías', 'central-tickets'); ?>
                                </a>
                            <?php endif; ?>

                            <a href="<?= esc_url(home_url()) ?>" class="button button-secondary home-button">
                                <span class="dashicons dashicons-admin-home"></span>
                                <?php esc_html_e('Ir al inicio', 'central-tickets'); ?>
                            </a>

                            <?php if ($contact_url = $this->get_contact_url()): ?>
                                <a href="<?= esc_url($contact_url) ?>" class="button button-secondary contact-button">
                                    <span class="dashicons dashicons-email"></span>
                                    <?php esc_html_e('Contactar soporte', 'central-tickets'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php $this->render_suggested_products(); ?>
                </div>
            </div>
        </div>

        <style>
            .product-not-available-form {
                max-width: 600px;
                margin: 20px auto;
                padding: 30px;
                background: #fff;
                border: 1px solid #ddd;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .not-available-message {
                text-align: center;
            }

            .message-icon .dashicons {
                font-size: 48px;
                color: #ff9800;
                margin-bottom: 20px;
            }

            .suggested-actions {
                margin-top: 30px;
            }

            .action-buttons {
                display: flex;
                flex-wrap: wrap;
                gap: 15px;
                justify-content: center;
                margin-top: 20px;
            }

            .action-buttons .button {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 12px 20px;
                text-decoration: none;
                border-radius: 4px;
                transition: all 0.3s ease;
            }

            .action-buttons .button:hover {
                transform: translateY(-2px);
            }

            .shop-button {
                background: #2271b1;
                color: white;
            }

            .categories-button {
                background: #72aee6;
                color: white;
            }

            .home-button {
                background: #50575e;
                color: white;
            }

            .contact-button {
                background: #00a32a;
                color: white;
            }

            @media (max-width: 600px) {
                .action-buttons {
                    flex-direction: column;
                }

                .action-buttons .button {
                    width: 100%;
                    justify-content: center;
                }
            }
        </style>
        <?php
    }

    /**
     * ✅ Obtener URL de la tienda con múltiples fallbacks
     */
    private function get_shop_url(): string
    {
        // ✅ Verificar si WooCommerce está activo
        if (!$this->is_woocommerce_active()) {
            return home_url();
        }

        // ✅ Método 1: wc_get_page_id
        $shop_page_id = wc_get_page_id('shop');
        if ($shop_page_id && $shop_page_id > 0) {
            $post_status = get_post_status($shop_page_id);
            if ($post_status === 'publish') {
                $shop_url = get_permalink($shop_page_id);
                if ($shop_url && !is_wp_error($shop_url)) {
                    return $shop_url;
                }
            }
        }

        // ✅ Método 2: wc_get_page_permalink
        if (function_exists('wc_get_page_permalink')) {
            $shop_url = wc_get_page_permalink('shop');
            if ($shop_url && $shop_url !== home_url() && !is_wp_error($shop_url)) {
                return $shop_url;
            }
        }

        // ✅ Método 3: get_option directamente
        $shop_page_id = get_option('woocommerce_shop_page_id');
        if ($shop_page_id) {
            $shop_url = get_permalink($shop_page_id);
            if ($shop_url && !is_wp_error($shop_url)) {
                return $shop_url;
            }
        }

        // ✅ Método 4: Buscar página con slug 'shop'
        $shop_page = get_page_by_path('shop');
        if ($shop_page && $shop_page->post_status === 'publish') {
            return get_permalink($shop_page->ID);
        }

        // ✅ Último fallback: home
        return home_url();
    }

    /**
     * ✅ Verificar si WooCommerce está activo
     */
    private function is_woocommerce_active(): bool
    {
        return class_exists('WooCommerce') && function_exists('wc_get_page_id');
    }

    /**
     * ✅ URL de categorías de productos
     */
    private function get_product_categories_url(): string
    {
        if (!$this->is_woocommerce_active()) {
            return $this->get_shop_url();
        }

        // URL base de categorías de productos
        $categories_url = home_url('/product-category/');

        // Verificar si existe alguna categoría
        $categories = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'number' => 1
        ]);

        if (!empty($categories) && !is_wp_error($categories)) {
            return $categories_url;
        }

        return $this->get_shop_url();
    }

    /**
     * ✅ URL de contacto (buscar página común)
     */
    private function get_contact_url(): ?string
    {
        // Buscar páginas comunes de contacto
        $contact_slugs = ['contacto', 'contact', 'contact-us', 'soporte', 'support'];

        foreach ($contact_slugs as $slug) {
            $page = get_page_by_path($slug);
            if ($page && $page->post_status === 'publish') {
                return get_permalink($page->ID);
            }
        }

        // Buscar por título
        $contact_titles = ['Contacto', 'Contact', 'Contact Us', 'Soporte', 'Support'];

        foreach ($contact_titles as $title) {
            $page = get_page_by_title($title);
            if ($page && $page->post_status === 'publish') {
                return get_permalink($page->ID);
            }
        }

        return null;
    }

    /**
     * ✅ Renderizar productos sugeridos
     */
    private function render_suggested_products(): void
    {
        if (!$this->is_woocommerce_active()) {
            return;
        }

        // Obtener productos en oferta o destacados
        $suggested_products = wc_get_featured_product_ids();

        if (empty($suggested_products)) {
            // Fallback: productos más vendidos
            $suggested_products = wc_get_product_ids_on_sale();
        }

        if (empty($suggested_products)) {
            return;
        }

        // Limitar a 3 productos
        $suggested_products = array_slice($suggested_products, 0, 3);

        ?>
        <div class="suggested-products">
            <h4><?php esc_html_e('Productos que podrían interesarte:', 'central-tickets'); ?></h4>
            <div class="products-grid">
                <?php foreach ($suggested_products as $product_id): ?>
                    <?php $product = wc_get_product($product_id); ?>
                    <?php if ($product && $product->is_purchasable()): ?>
                        <div class="suggested-product">
                            <a href="<?= esc_url($product->get_permalink()) ?>">
                                <?= wp_kses_post($product->get_image('thumbnail')) ?>
                                <span class="product-title"><?= esc_html($product->get_name()) ?></span>
                                <span class="product-price"><?= wp_kses_post($product->get_price_html()) ?></span>
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <style>
            .suggested-products {
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #eee;
            }

            .products-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                gap: 15px;
                margin-top: 15px;
            }

            .suggested-product {
                text-align: center;
                padding: 10px;
                border: 1px solid #eee;
                border-radius: 4px;
                transition: transform 0.3s ease;
            }

            .suggested-product:hover {
                transform: translateY(-3px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }

            .suggested-product a {
                text-decoration: none;
                color: inherit;
            }

            .suggested-product img {
                width: 60px;
                height: 60px;
                object-fit: cover;
                border-radius: 4px;
            }

            .product-title {
                display: block;
                font-size: 12px;
                margin: 8px 0 4px 0;
                line-height: 1.3;
            }

            .product-price {
                display: block;
                font-weight: bold;
                color: #2271b1;
                font-size: 14px;
            }
        </style>
        <?php
    }
}