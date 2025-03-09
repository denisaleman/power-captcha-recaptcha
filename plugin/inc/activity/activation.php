<?php
/**
 * Activity feature activation functions
 *
 * @package PowerCaptchaReCaptcha/Activity/Activation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Activity feature activation handler
 *
 * @since 1.0.11
 *
 * @return void
 */
function pwrcap_activity_activation() {
	require_once PWRCAP_DIR . '/inc/activity/migration.php';
	pwrcap_create_captcha_activity_table();
}
register_activation_hook( PWRCAP_PLUGIN_FILE, 'pwrcap_activity_activation' );
