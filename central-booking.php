<?php
/**
 * Plugin Name: Central Reservas
 * Description: Gestión de cabotaje para Galápagos.
 * Version: 1.0
 * Author: OceanIA Galápagos
 * Author URI: https://github.com/AnsExp
 * License: GPL2
 * Requires Plugins: woocommerce
 * Requires PHP: 8.1.0
 */

defined('ABSPATH') || exit;

if (!defined('CENTRAL_BOOKING_DIR')) {
    define('CENTRAL_BOOKING_DIR', plugin_dir_path(__FILE__));
}

if (!defined('CENTRAL_BOOKING_URL')) {
    define('CENTRAL_BOOKING_URL', plugin_dir_url(__FILE__));
}

$vendor = CENTRAL_BOOKING_DIR . 'vendor/autoload.php';
if (file_exists($vendor)) {
    require_once $vendor;
}

$bootstrapFile = CENTRAL_BOOKING_DIR . 'src/Bootstrap.php';
if (!class_exists(\CentralBooking\Bootstrap::class) && file_exists($bootstrapFile)) {
    require_once $bootstrapFile;
}

if (class_exists(\CentralBooking\Bootstrap::class)) {
    \CentralBooking\Bootstrap::init();
} else {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p>Central Tickets: Bootstrap class not found. Ensure <code>vendor/</code> is installed and plugin files are complete.</p></div>';
    });
    return;
}

require_once 'includes/git-functions-utils.php';
require_once 'includes/git-hooks.php';

require_once 'packages/qr/functions.php';
require_once 'packages/gui/functions.php';
require_once 'packages/pdf/functions.php';
require_once 'packages/data/functions.php';
require_once 'packages/webhook/functions.php';