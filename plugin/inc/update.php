<?php
/**
 * Plugin update functions.
 *
 * Handles database version updates for the PowerCaptchaReCaptcha plugin.
 *
 * @package PowerCaptchaReCaptcha/Update
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checks and updates the stored database version of the plugin.
 *
 * This function retrieves the current stored version of the plugin from the database.
 * If the stored version is older than the defined `PWRCAP_VERSION`, it updates it.
 *
 * @return void
 */
function pwrcap_maybe_db_version_update() {
	$db_version = pwrcap_option( 'plugin', 'version' );
	$db_version = null === $db_version ? '0.0.0' : $db_version;
	if ( version_compare( $db_version, PWRCAP_VERSION, '<' ) ) {
		pwrcap_update_option( 'plugin', 'version', PWRCAP_VERSION );
	}
}
add_action( 'pwrcap_init', 'pwrcap_maybe_db_version_update', PHP_INT_MAX );
