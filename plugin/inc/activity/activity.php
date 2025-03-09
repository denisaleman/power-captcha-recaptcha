<?php
/**
 * Bootstrap Activity feature
 *
 * @package PowerCaptchaReCaptcha/Activity
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disables tracking activity if the `PWRCAP_DISABLE_TRACKING_ACTIVITY` constant is set.
 *
 * This condition checks if the `PWRCAP_DISABLE_TRACKING_ACTIVITY` constant is defined
 * and whether its value is `true` or `1`. If so, the function immediately returns,
 * effectively disabling the tracking feature.
 *
 * Usage:
 * - Define `PWRCAP_DISABLE_TRACKING_ACTIVITY` in `wp-config.php` or a plugin file to disable tracking.
 * - Accepted values: `true` or `1`
 *
 * Example:
 * ```php
 * define( 'PWRCAP_DISABLE_TRACKING_ACTIVITY', true );
 * ```
 *
 * @return void Stops execution if tracking is disabled.
 */
if (
	defined( 'PWRCAP_DISABLE_TRACKING_ACTIVITY' ) &&
	( PWRCAP_DISABLE_TRACKING_ACTIVITY === true ||
	PWRCAP_DISABLE_TRACKING_ACTIVITY === 1 ||
	PWRCAP_DISABLE_TRACKING_ACTIVITY === '1' )
) {
	return;
}

require_once PWRCAP_DIR . '/inc/activity/activation.php';
require_once PWRCAP_DIR . '/inc/activity/update.php';
require_once PWRCAP_DIR . '/inc/activity/admin.php';

/**
 * Track verification response activity.
 *
 * This function tracks the activity for captcha verification responses. If the captcha
 * is solved successfully, it increments the solved counter. If the captcha fails,
 * it increments the failed counter. The captcha type can be customized using the
 * 'pwrcap_track_captcha_type' filter.
 *
 * @since 1.0.11
 *
 * @param object $response The response object from the captcha verification.
 * @return object The same response object, unmodified.
 */
function pwrcap_track_verification_response( $response ) {
	require_once PWRCAP_DIR . '/inc/activity/PwrcapCaptchaActivityRecord.php';

	if ( ! PwrcapCaptchaActivityRecord::db_table_exists() ) {
		return $response;
	}

	$is_solved    = (bool) $response->isSuccess();
	$captcha_type = apply_filters( 'pwrcap_track_captcha_type', 'common' );

	if ( $is_solved ) {
		PwrcapCaptchaActivityRecord::add_solved_captcha( $captcha_type );
	} else {
		PwrcapCaptchaActivityRecord::add_failed_captcha( $captcha_type );
	}

	return $response;
}
add_filter( 'pwrcap_verification_response', 'pwrcap_track_verification_response', 10, 1 );

/**
 * Track empty captcha submissions.
 *
 * This function tracks instances where no captcha code is sent during form submission.
 * It increments the empty counter for the specified captcha type, which can be customized
 * using the 'pwrcap_track_captcha_type' filter.
 *
 * @since 1.0.11
 *
 * @return void
 */
function pwrcap_track_no_captcha_code_sent() {
	require_once PWRCAP_DIR . '/inc/activity/PwrcapCaptchaActivityRecord.php';

	if ( ! PwrcapCaptchaActivityRecord::db_table_exists() ) {
		return;
	}

	$captcha_type = apply_filters( 'pwrcap_track_captcha_type', 'common' );

	PwrcapCaptchaActivityRecord::add_empty_captcha( $captcha_type );
}
add_action( 'pwrcap_no_captcha_code_sent', 'pwrcap_track_no_captcha_code_sent' );
