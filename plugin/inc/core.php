<?php
/**
 * Core functions.
 *
 * @package PowerCaptchaReCaptcha/Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Plugin activation handler
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_activation() {
	if ( ! wp_next_scheduled( 'pwrcap_daily_event' ) ) {
		wp_schedule_event( time(), 'daily', 'pwrcap_daily_event' );
	}
}
register_activation_hook( PWRCAP_PLUGIN_FILE, 'pwrcap_activation' );

/**
 * Plugin deactivation handler.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_deactivation() {
	wp_clear_scheduled_hook( 'pwrcap_daily_event' );
}
register_deactivation_hook( PWRCAP_PLUGIN_FILE, 'pwrcap_deactivation' );

/**
 * Load frontend scripts.
 *
 * @since 1.0.0
 */
function pwrcap_load_frontend() {
	if ( true !== pwrcap_is_setup_complete() ) {
		return;
	}

	$captcha_type = pwrcap_get_captcha_type();

	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	$site_key = pwrcap_option( 'general', 'site_key' );

	wp_enqueue_script( 'pwrcap-captcha', PWRCAP_URL . '/assets/js/captcha' . $min . '.js', array(), PWRCAP_VERSION, false );
	wp_localize_script(
		'pwrcap-captcha',
		'pwrcap',
		apply_filters( 'pwrcap_localized_data', array( 'site_key' => $site_key ) )
	);

	$api_url = pwrcap_get_captcha_api_url( $captcha_type );

	if ( ! empty( $api_url ) ) {
		wp_enqueue_script( 'pwrcap-api', $api_url, array(), PWRCAP_VERSION, false );
	}

	wp_enqueue_style( 'pwrcap-style', PWRCAP_URL . '/assets/css/captcha' . $min . '.css', array(), PWRCAP_VERSION );
}
add_action( 'login_enqueue_scripts', 'pwrcap_load_frontend' );
add_action( 'wp_enqueue_scripts', 'pwrcap_load_frontend' );

/**
 * Display captcha wrapper.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_render_captcha_wrapper() {
	$captcha_type = pwrcap_get_captcha_type();
	if ( 'v2cbx' === $captcha_type ) {
		$class = '';
	} elseif ( 'v2inv' === $captcha_type ) {
		$class = ' pwrcap-wrapper_invisible';
	}

	if ( true === apply_filters( 'pwrcap_prevent_render_captcha', false, current_action() ) ) {
		return;
	}
	if ( true === apply_filters( 'pwrcap_prevent_render_captcha_wrapper', false, current_action() ) ) {
		return;
	}
	echo '<div class="pwrcap-wrapper' . esc_attr( $class ) . '" data-context="' . esc_attr( current_action() ) . '"></div>';
}

/**
 * Display captcha input.
 *
 * @since 1.0.0
 */
function pwrcap_render_captcha_input() {
	if ( true === apply_filters( 'pwrcap_prevent_render_captcha', false, current_action() ) ) {
		return;
	}
	if ( true === apply_filters( 'pwrcap_prevent_render_captcha_input', false, current_action() ) ) {
		return;
	}
	echo '<input type="hidden" name="g-recaptcha-response" class="g-recaptcha-response">';
}

/**
 * Debug process login form
 *
 * @since 1.0.0
 *
 * @param ReCaptcha\Response $response Response object retrieved during verification process.
 */
function pwrcap_debug_verification_response( $response ) {
	add_filter( 'pwrcap_localized_data',
		function( $data ) use ( $response ) {
			$data['debug'] = $response->toArray();
			return $data;
		}
	);
	return $response;
}

// Load admin.
require_once PWRCAP_DIR . '/inc/wp/common.php';
require_once PWRCAP_DIR . '/inc/wp/login.php';
require_once PWRCAP_DIR . '/inc/wp/register.php';
require_once PWRCAP_DIR . '/inc/wp/lostpassword.php';
require_once PWRCAP_DIR . '/inc/wp/resetpassword.php';
require_once PWRCAP_DIR . '/inc/wp/comment.php';

require_once PWRCAP_DIR . '/inc/woo/common.php';
require_once PWRCAP_DIR . '/inc/woo/login.php';
require_once PWRCAP_DIR . '/inc/woo/register.php';
require_once PWRCAP_DIR . '/inc/woo/lostpassword.php';
require_once PWRCAP_DIR . '/inc/woo/resetpassword.php';
require_once PWRCAP_DIR . '/inc/woo/checkout.php';

/**
 * Register hooks.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_init() {
	if ( true !== pwrcap_is_setup_complete() ) {
		return;
	}

	if ( pwrcap_option( 'misc', 'enable_debug' ) ) {
		add_filter( 'pwrcap_verification_response', 'pwrcap_debug_verification_response', 11, 1 );
	}

	do_action( 'pwrcap_add_captcha_handler' );
}
add_action( 'plugins_loaded', 'pwrcap_init' );

/**
 * Add plugin settings link.
 *
 * @since 1.0.0
 *
 * @param array $links Links.
 * @return array Modified links.
 */
function pwrcap_add_plugin_action_links( $links ) {
	return array_merge( array( 'settings' => '<a href="' . esc_url( admin_url( 'options-general.php?page=pwrcap-settings' ) ) . '">' . esc_html__( 'Settings', 'power-captcha-recaptcha' ) . '</a>' ), $links );
}
add_filter( 'plugin_action_links_' . PWRCAP_BASENAME, 'pwrcap_add_plugin_action_links' );

/**
 * Load language.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_load_language() {
	load_plugin_textdomain( 'power-captcha-recaptcha', false, dirname( plugin_basename( PWRCAP_PLUGIN_FILE ) ) . '/languages' );
}
add_action( 'init', 'pwrcap_load_language' );

/**
 * Validate posted captcha.
 *
 * @since 1.0.0
 *
 * @return bool True if valid.
 */
function pwrcap_validate_posted_captcha() {
	$grecaptcha_response = '';
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- not the function's responsibility
	if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] && ! empty( $_POST['g-recaptcha-response'] ) ) {
		$grecaptcha_response = sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) );
		// phpcs:enable
	}

	$server_name = null;
	// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( ! empty( $_SERVER['SERVER_NAME'] ) ) {
		$server_name = $_SERVER['SERVER_NAME'];
		// phpcs:enable
	}

	$ip_address = null;
	if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip_address = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
	}

	$secret_key = pwrcap_option( 'general', 'secret_key' );
	$recaptcha  = new \ReCaptcha\ReCaptcha( $secret_key );

	/**
	 * Verify Captcha: Implement `setExpectedAction` and `setScoreThreshold`:
	 * ->setExpectedAction( 'homepage' )
	 * ->setScoreThreshold( 0.5 )
	 *
	 * @todo phpcs:ignore Generic.Commenting.Todo.CommentFound
	 */

	$response = $recaptcha->setExpectedHostname( $server_name )->verify( $grecaptcha_response, $ip_address );
	$response = apply_filters( 'pwrcap_verification_response', $response );

	return (bool) $response->isSuccess();
}
