<?php
/**
 * Power Captcha ReCaptcha Activity Report
 *
 * This template provides a summary of captcha activity, including the number of solved,
 * failed, and empty captcha attempts for the last 7 days. This is useful for administrators
 * to monitor the captcha's performance and user interaction with Contact Forms 7.
 *
 * @package PowerCaptchaReCaptcha/Activity/Templates
 * @since 1.0.11
 */

?>

<?php
require_once PWRCAP_DIR . '/inc/activity/PwrcapCaptchaActivityRecord.php';
$pwrcap_table_exists = PwrcapCaptchaActivityRecord::db_table_exists();
if ( $pwrcap_table_exists ) {
	$pwrcap_activity_data = PwrcapCaptchaActivityRecord::get_captcha_activity_data( 7 );
}
$pwrcap_activity_table_creation_error = (bool) pwrcap_option( 'plugin', 'activity_table_creation_error' );
?>

<h2><?php esc_html_e( 'Activity Report', 'power-captcha-recaptcha' ); ?></h2>

<?php do_action( 'pwrcap_do_activity_section' ); ?>

<?php if ( $pwrcap_table_exists ) : ?>

<div class="activity-list">
	<div class="activity-list__item activity-list-item">
		<div class="activity-list-item__label"><?php esc_html_e( 'Solved', 'power-captcha-recaptcha' ); ?></div>
		<div class="activity-list-item__value"><?php echo (int) array_sum( $pwrcap_activity_data['solved_counts'] ); ?></div>
	</div>
	<div class="activity-list__item activity-list-item">
		<div class="activity-list-item__label"><?php esc_html_e( 'Failed', 'power-captcha-recaptcha' ); ?></div>
		<div class="activity-list-item__value"><?php echo (int) array_sum( $pwrcap_activity_data['failed_counts'] ); ?></div>
	</div>
	<div class="activity-list__item activity-list-item">
		<div class="activity-list-item__label"><?php esc_html_e( 'Empty', 'power-captcha-recaptcha' ); ?></div>
		<div class="activity-list-item__value"><?php echo (int) array_sum( $pwrcap_activity_data['empty_counts'] ); ?></div>
	</div>
</div>

<canvas id="captchaActivityReportChart"></canvas>

<?php else : ?>
	<?php if ( ! $pwrcap_activity_table_creation_error ) : ?>
	<p><?php esc_html_e( 'Database update required to use this feature.', 'power-captcha-recaptcha' ); ?></p>
	<a class="button" href="
		<?php
		echo esc_url(
			add_query_arg(
				array(
					'pwrcap_update_activity_table' => 1,
					'_wpnonce'                     => wp_create_nonce( 'pwrcap_update_activity_table_nonce' ),
				),
				admin_url()
			)
		);
		?>
		">
		<?php esc_html_e( 'Update', 'power-captcha-recaptcha' ); ?>
	</a>
	<?php else : ?>
	<p><?php esc_html_e( 'Database update required to use this feature, but the following error ocurred:', 'power-captcha-recaptcha' ); ?></p>
	<p><?php echo esc_html( $pwrcap_activity_table_creation_error ); ?></p>
	<a class="button" href="
		<?php
		echo esc_url(
			add_query_arg(
				array(
					'pwrcap_update_activity_table' => 1,
					'_wpnonce'                     => wp_create_nonce( 'pwrcap_update_activity_table_nonce' ),
				),
				admin_url()
			)
		);
		?>
		">
		<?php esc_html_e( 'Retry Update', 'power-captcha-recaptcha' ); ?>
	</a>
	<?php endif; ?>
<?php endif; ?>
