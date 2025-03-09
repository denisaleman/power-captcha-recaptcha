<?php
/**
 * Activity feature update functions
 *
 * @package PowerCaptchaReCaptcha/Activity/Update
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Activity feature activation handler.
 *
 * Checks for a valid GET request with a nonce before executing a database update.
 * Redirects back to the referring page after execution.
 *
 * @since 1.0.11
 *
 * @return void
 */
function pwrcap_force_activity_db_update() {
	if ( ! isset( $_GET['pwrcap_update_activity_table'] ) || ! isset( $_GET['_wpnonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'pwrcap_update_activity_table_nonce' ) ) {
		// phpcs:ignore WordPress.WP.I18n.MissingArgDomain, WordPress.Security.EscapeOutput.OutputNotEscaped
		wp_die( __( 'Invalid request.' ) );
	}

	require_once PWRCAP_DIR . '/inc/activity/migration.php';
	pwrcap_create_captcha_activity_table();

	$referer_url = ! empty( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
	wp_safe_redirect( $referer_url );
	exit;
}
add_action( 'init', 'pwrcap_force_activity_db_update' );

/**
 * Activity feature activation handler
 *
 * @since 1.0.11
 *
 * @return void
 */
function pwrcap_activity_db_update() {
	$db_version = pwrcap_option( 'plugin', 'version' );
	$db_version = null === $db_version ? '0.0.0' : $db_version;
	if ( version_compare( $db_version, '1.0.11', '<' ) ) {
		require_once PWRCAP_DIR . '/inc/activity/migration.php';
		$result = pwrcap_create_captcha_activity_table();
		pwrcap_update_option( 'plugin', 'activity_table_creation_error', is_wp_error( $result ) ? $result : false );
	}
}
add_action( 'pwrcap_init', 'pwrcap_activity_db_update' );


