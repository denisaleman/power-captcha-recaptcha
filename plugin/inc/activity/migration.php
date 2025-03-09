<?php
/**
 * Activity migrations
 *
 * @package PowerCaptchaReCaptcha/Activity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Run the database migration to create the captcha activity table.
 *
 * @since 1.0.11
 * @return void|WP_Error
 */
function pwrcap_create_captcha_activity_table() {
	require_once PWRCAP_DIR . '/inc/activity/PwrcapCaptchaActivityRecord.php';
	global $wpdb;

	$table_name      = PwrcapCaptchaActivityRecord::get_table_name();
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		captcha_type VARCHAR(50) NOT NULL,
		status ENUM('" . PwrcapCaptchaActivityRecord::STATUS_FAILED . "', '" . PwrcapCaptchaActivityRecord::STATUS_SOLVED . "', '" . PwrcapCaptchaActivityRecord::STATUS_EMPTY . "') NOT NULL,
		created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
		PRIMARY KEY (id)
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	if ( ! PwrcapCaptchaActivityRecord::db_table_exists() ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
		$error = ! empty( $wpdb->last_error ) ? $wpdb->last_error : __( 'Unknown error.', 'power-captcha-recaptcha' );
		/* translators: %s: Error message */
		$error = new WP_Error( 'pwrcap_stat_records_table_not_created', sprintf( __( "Could't create table: %s", 'power-captcha-recaptcha' ), $error ) );
		pwrcap_update_option( 'plugin', 'activity_table_creation_error', is_wp_error( $error ) ? $error : false );
	}
}

/**
 * Delete the captcha activity table.
 *
 * @since 1.0.11
 * @return void
 */
function pwrcap_delete_captcha_activity_table() {
	require_once PWRCAP_DIR . '/inc/activity/PwrcapCaptchaActivityRecord.php';
	global $wpdb;

	$table_name = PwrcapCaptchaActivityRecord::get_table_name();

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query(
		"DROP TABLE IF EXISTS {$table_name}" // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
}
