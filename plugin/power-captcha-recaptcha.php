<?php
/**
 * Plugin Name:       Power Captcha reCAPTCHA
 * Plugin URI:        https://wordpress.org/plugins/power-captcha-recaptcha/
 * Version:           1.1.0
 * Description:       Google reCAPTCHA integration for WordPress and WooCommerce.
 * Author:            Denis Alemán
 * Author URI:        https://denisaleman.com/
 * Requires at least: 5.0
 * Tested up to:      6.8.0
 * WC tested up to:   9.7.1
 * Requires PHP:      5.5
 * Text Domain:       power-captcha-recaptcha
 * Domain Path:       /languages
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   PowerCaptchaReCaptcha
 * @version   1.1.0
 * @author    Denis Alemán
 * @copyright 2023-2025 Denis Alemán
 * @license   GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'PWRCAP_VERSION', '1.1.0' );
define( 'PWRCAP_BASENAME', plugin_basename( __FILE__ ) );
define( 'PWRCAP_PLUGIN_FILE', __FILE__ );
define( 'PWRCAP_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
define( 'PWRCAP_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );

if ( file_exists( PWRCAP_DIR . '/vendor/autoload.php' ) ) {
	require_once PWRCAP_DIR . '/vendor/autoload.php';
}

require_once PWRCAP_DIR . '/inc/api.php';
require_once PWRCAP_DIR . '/inc/admin.php';
require_once PWRCAP_DIR . '/inc/update.php';
require_once PWRCAP_DIR . '/inc/core.php';
