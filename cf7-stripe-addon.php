<?php

/**
 * Plugin Name: Accept Stripe Payment for Contact Form 7
 * Description: The easiest way to add STRIPE payment functionality to build your one-time and recurring payment form together with contact form 7 without creating an entire online store.
 * Version:     1.1.1
 * Author:    	Payment Addons, support@payaddons.com 
 * Author URI:	https://payaddons.com
 * Text Domain: contact-form-7-stripe-addon
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins: contact-form-7 
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Defining plugin constants.
 *
 * @since 1.0.0
 */
define('CF7PA_PLUGIN_NAME', 'Stripe Payment for Contact from 7');
define('CF7PA_PLUGIN_VERSION', '1.1.1');
define('CF7PA_PLUGIN_URL', 'https://payaddons.com/');
define('CF7PA_PLUGIN_TEMPLATE_URL', 'https://api.payaddons.com/cf7');
define('CF7PA_ADDONS_REST_API', 'cf7pa/v1/');
define('CF7PA_ADDONS_FILE', __FILE__);
define('CF7PA_ADDONS_BASENAME', plugin_basename(__FILE__));
define('CF7PA_ADDONS_PATH', trailingslashit(plugin_dir_path(__FILE__)));
define('CF7PA_ADDONS_ASSET_PATH', CF7PA_ADDONS_PATH . '/assets/');
define('CF7PA_ADDONS_URL', trailingslashit(plugins_url('/', __FILE__)));
define('CF7PA_ADDONS_ASSET_URL', CF7PA_ADDONS_URL . '/assets/');
define('CF7PA_ADDONS_LOG_FOLDER', plugin_dir_path(__FILE__) . 'logs');

if ( ! function_exists( 'cf7pa_fs' ) ) {
	require_once('freemius-config.php');
} else {
	cf7pa_fs()->set_basename( false, __FILE__ );
}
if ( ! class_exists( '\Stripe\Stripe' ) ) {
	require_once CF7PA_ADDONS_PATH . '/libs/stripe-php/init.php';
}
require_once CF7PA_ADDONS_PATH . '/autoload.php';
require_once CF7PA_ADDONS_PATH . '/bootstrap.php';
require_once CF7PA_ADDONS_PATH . '/includes/functions.php';

/**
 * Run plugin after all others plugins
 *
 * @since 1.0.0
 */
add_action( 'plugins_loaded', function() {
	\CF7PA_Pay_Addons\Bootstrap::instance();
} );

/**
 * Activation hook
 *
 * @since v1.0.0
 */
register_activation_hook(__FILE__, function () {
	register_uninstall_hook( __FILE__, 'CF7PA_plugin_uninstall' );
});

/**
 * Deactivation hook
 *
 * @since v1.0.0
 */
register_deactivation_hook(__FILE__, function () {
});

/**
 * Handle uninstall
 *
 * @since v1.0.0
 */
if ( !function_exists('cf7pa_plugin_uninstall') ) {
	function cf7pa_plugin_uninstall(){
		if (!get_option('cf7pa_keep_data')) {
			// Delete options.
			delete_option( 'cf7pa_stripe_settings' );
			delete_option( 'cf7pa_sys_settings' );
		}
		
		cf7pa_fs()->add_action('after_uninstall', 'cf7pa_fs_uninstall_cleanup');
	}
}