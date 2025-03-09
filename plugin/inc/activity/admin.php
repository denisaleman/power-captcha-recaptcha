<?php
/**
 * Activity Admin functions
 *
 * @package PowerCaptchaReCaptcha/Activity/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Print a link for the Activity tab in admin navigation.
 *
 * This function adds a "Activity" tab link to the admin navigation in the settings page
 * of the Power Captcha ReCaptcha plugin.
 *
 * @since 1.0.11
 *
 * @return void
 */
function pwrcap_print_activity_tab_link() {
	?>
	<a href="#tab-activity" class="nav-tab"><?php esc_html_e( 'Activity', 'power-captcha-recaptcha' ); ?></a>
	<?php
}
add_action( 'pwrcap_admin_do_tab_navigation', 'pwrcap_print_activity_tab_link', 25 );

/**
 * Print content for the activity tab in admin navigation.
 *
 * This function outputs the content for the "Activity" tab in the admin panel of
 * the Power Captcha ReCaptcha plugin. It includes the activity report template.
 *
 * @since 1.0.11
 *
 * @return void
 */
function pwrcap_print_activity_tab_content() {
	?>
	<div id="tab-activity" class="pwrcap-tab-content meta-box-sortables ui-sortable">
		<div class="postbox">
			<div class="inside">
				<form class="pwrcap_settings_form" action="options.php" method="post">
					<?php include PWRCAP_DIR . '/inc/activity/templates/activity-report.php'; ?>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
	</div><!-- .meta-box-sortables -->
	<?php
}
add_action( 'pwrcap_admin_do_tab_stage', 'pwrcap_print_activity_tab_content', 25 );

/**
 * Enqueue the necessary chart scripts.
 *
 * This function enqueues the required JavaScript files for rendering the activity chart
 * on the Power Captcha ReCaptcha admin page. It loads the Chart.js library and a custom
 * script that initializes the chart using localized data.
 *
 * @param string $hook The current admin page hook name.
 * @return void
 */
function pwrcap_enqueue_chart_script( $hook ) {
	if ( 'settings_page_pwrcap-settings' !== $hook ) {
		return;
	}

	require_once PWRCAP_DIR . '/inc/activity/PwrcapCaptchaActivityRecord.php';

	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	wp_enqueue_script(
		'chart-js',
		PWRCAP_URL . '/inc/activity/assets/js/chart' . $min . '.js',
		array( 'jquery' ),
		PWRCAP_VERSION,
		true
	);

	wp_enqueue_script(
		'activity-init',
		PWRCAP_URL . '/inc/activity/assets/js/init' . $min . '.js',
		array( 'chart-js' ),
		PWRCAP_VERSION,
		true
	);

	if ( PwrcapCaptchaActivityRecord::db_table_exists() ) {
		$pwrcap_activity_data = PwrcapCaptchaActivityRecord::get_captcha_activity_data( 7 );
	}
	wp_localize_script( 'activity-init', 'pwrcapActivityData', isset( $pwrcap_activity_data ) ? $pwrcap_activity_data : array() );
}
add_action( 'admin_enqueue_scripts', 'pwrcap_enqueue_chart_script' );

/**
 * Enqueue the necessary chart style.
 *
 * This function enqueues the required CSS file for styling the activity chart
 * on the Power Captcha ReCaptcha admin page.
 *
 * @param string $hook The current admin page hook name.
 * @return void
 */
function pwrcap_enqueue_chart_style( $hook ) {
	if ( 'settings_page_pwrcap-settings' !== $hook ) {
		return;
	}

	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	wp_enqueue_style(
		'activity',
		PWRCAP_URL . '/inc/activity/assets/css/activity' . $min . '.css',
		array(),
		PWRCAP_VERSION,
		'all'
	);
}
add_action( 'admin_enqueue_scripts', 'pwrcap_enqueue_chart_style' );

add_action( 'pwrcap_do_activity_section', 'pwrcap_complete_setup_message' );

