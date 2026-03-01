<?php
namespace CentralBooking\WooCommerce\SingleProduct;

use CentralBooking\GUI\DisplayerInterface;
use CentralBooking\GUI\ModalComponent;
use CentralBooking\GUI\Constants\ButtonStyleConstants;
use WC_Product_Operator;

class FormProduct implements DisplayerInterface
{
    public const ACTION_NONCE = 'git_product_form';

    public function __construct(private readonly WC_Product_Operator $product)
    {
    }

    public function render()
    {
        $modal = new ModalComponent('Información');
        $modal->add_body(git_string_to_component('<div id="message_form_modal" class="mb-3"></div>'));
        $button_dimiss = $modal->create_button_dimiss('Entendido');
        $button_dimiss->set_style(ButtonStyleConstants::WARNING);
        $button_launch = $modal->create_button_launch();
        $modal->add_body($button_dimiss);
        $button_launch->id = 'button_launch_modal_form';
        $button_launch->styles->set('display', 'none');
        $this->load_scripts();

        $modal->render();
        $button_launch->render();

        $action = add_query_arg(
            ['action' => 'git_product_submit'],
            admin_url('admin-ajax.php')
        );
        ?>
        <script> window.CentralTickets = window.CentralTickets || {}; </script>
        <div id="overlay_loading" class="overlay">
            <div class="spinner-border text-secondary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <p class="git_product_description"><?= $this->product->get_description() ?></p>
        <form id="product_form" class="p-3" method="post" action="<?= $action ?>">
            <input type="hidden" name="product" value="<?= $this->product->get_id() ?>">
            <?php
            git_nonce_field();
            (new FormProductRoute($this->product))->render();
            (new FormProductTransport($this->product))->render();
            (new FormProductPassenger)->render();
            ?>
        </form>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
            crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
        <?php
    }

    private function load_scripts()
    {
        wp_enqueue_style(
            'operator-product-styles',
            CENTRAL_BOOKING_URL . '/assets/css/product-form.css',
            [],
            time(),
        );

        wp_enqueue_script(
            'git-form-product',
            CENTRAL_BOOKING_URL . '/assets/js/client/product/product-form.js',
            [],
            time(),
            true
        );
    }
}
