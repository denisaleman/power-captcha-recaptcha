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

	wp_enqueue_script( 'pwrcap-captcha', PWRCAP_URL . '/assets/dist/js/captcha' . $min . '.js', array(), PWRCAP_VERSION, false );
	wp_localize_script(
		'pwrcap-captcha',
		'pwrcap',
		apply_filters( 'pwrcap_localized_data', array( 'site_key' => $site_key ) )
	);

	$api_url = pwrcap_get_captcha_api_url( $captcha_type );

	if ( ! empty( $api_url ) ) {
		wp_enqueue_script( 'pwrcap-api', $api_url, array(), PWRCAP_VERSION, false );
	}

	wp_enqueue_style( 'pwrcap-style', PWRCAP_URL . '/assets/dist/css/captcha' . $min . '.css', array(), PWRCAP_VERSION );
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

require_once PWRCAP_DIR . '/inc/cf7/common.php';
require_once PWRCAP_DIR . '/inc/cf7/cf7.php';

require_once PWRCAP_DIR . '/inc/activity/activity.php';

/**
 * Register hooks.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_init() {
	do_action( 'pwrcap_init' );

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
 * @return bool True if valid. False if invalid or empty.
 */
function pwrcap_validate_posted_captcha() {
	$grecaptcha_code = pwrcap_get_posted_captcha_code();

	return pwrcap_is_valid_captcha_code( $grecaptcha_code );
}

/**
 * Verify reCAPTCHA with Google's servers.
 *
 * @since 1.3.0
 *
 * @param string $captcha_code Captcha code.
 * @return bool True if valid. False if invalid.
 */
function pwrcap_verify_google_recaptcha_code( $captcha_code ) {
	if ( ! $captcha_code ) {
		/**
		 * Fires if captcha code is an empty string or has not been sent through the form.
		 *
		 * @since 1.0.9
		 */
		do_action( 'pwrcap_no_captcha_code_sent' );
		return false;
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
	 * Verify Captcha: Implement `setExpectedAction`:
	 * ->setExpectedAction( 'homepage' )
	 *
	 * @todo phpcs:ignore Generic.Commenting.Todo.CommentFound
	 */

	/**
	 * Filter the score threshold for reCAPTCHA v3 verification.
	 *
	 * This filter allows developers to adjust the minimum score required for
	 * reCAPTCHA v3 to consider a user interaction as human. For reCAPTCHA v3,
	 * Google returns a score between 0.0 and 1.0 where higher scores indicate
	 * more legitimate traffic. The default threshold is 0.5.
	 *
	 * @since 1.3.0
	 *
	 * @param int $score_threshold The score threshold for reCAPTCHA v3 validation.
	 *                             Value should be between 0 and 1. Default 0.5.
	 */
	$score_threshold = apply_filters( 'pwrcap_verification_score_threshold', 0.5 );

	if ( 'v3' === pwrcap_get_captcha_type() ) {
		$recaptcha->setScoreThreshold( $score_threshold );
	}

	/**
	 * Filters the expected hostname used during server-side reCAPTCHA verification.
	 *
	 * By default, the plugin validates that the hostname returned by Google's
	 * verification service matches the current server hostname. This provides an
	 * additional security check beyond the standard reCAPTCHA verification.
	 *
	 * This filter allows developers to override the expected hostname. A common
	 * use case is automated testing with Google's official test keys, which always
	 * return the hostname `testkey.google.com` instead of the actual site hostname.
	 *
	 * @since 1.3.0
	 *
	 * @param string $expected_hostname The expected hostname. Defaults to the current server hostname.
	 */
	$expected_hostname = apply_filters( 'pwrcap_recaptcha_expected_hostname', $server_name );
	$response          = $recaptcha->setExpectedHostname( $expected_hostname )->verify( $captcha_code, $ip_address );
	$response          = apply_filters( 'pwrcap_verification_response', $response );

	return (bool) $response->isSuccess();
}

/**
 * Check if captcha code is valid.
 *
 * @since 1.0.9
 *
 * @param string $captcha_code Captcha code.
 *
 * @return bool True if valid. False if invalid or empty.
 */
function pwrcap_is_valid_captcha_code( $captcha_code ) {
	if ( ! $captcha_code ) {
		/**
		 * Fires if captcha code is an empty string or has not been sent through the form.
		 *
		 * @since 1.0.9
		 */
		do_action( 'pwrcap_no_captcha_code_sent' );
		return false;
	}

	/**
	 * Filter the CAPTCHA verification function.
	 *
	 * This filter allows developers to change the function used to verify the
	 * CAPTCHA response code. By default, the plugin uses 'pwrcap_verify_google_recaptcha_code'
	 * to validate reCAPTCHA responses with Google's servers. This filter can be used
	 * to replace the verification method with a custom implementation.
	 *
	 * @since 1.3.0
	 *
	 * @param string $verify_function The name of the function to use for CAPTCHA verification.
	 *                                Defaults to 'pwrcap_verify_google_recaptcha_code'.
	 */
	$verify_function       = apply_filters( 'pwrcap_verify_function', 'pwrcap_verify_google_recaptcha_code' );
	$is_valid_captcha_code = $verify_function( $captcha_code );

	return apply_filters( 'pwrcap_is_valid_captcha_code', $is_valid_captcha_code, $captcha_code );
}

/**
 * Retrieves the posted CAPTCHA response code from a submitted form.
 *
 * This function checks if the request method is POST and then attempts
 * to retrieve the CAPTCHA code from the specified POST field. If the request
 * method is not POST, it returns an empty string. The function also sanitizes
 * the retrieved CAPTCHA code to ensure safe handling.
 *
 * @since 1.0.9
 *
 * @param string $key Optional. The key of the CAPTCHA response in the POST data.
 *                    Defaults to 'g-recaptcha-response'.
 *
 * @return string The sanitized CAPTCHA response code if available, or an empty
 *                string if the request method is not POST or the CAPTCHA field
 *                is not set.
 */
function pwrcap_get_posted_captcha_code( $key = 'g-recaptcha-response' ) {
	$captcha_code = '';

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- not the function's responsibility
	if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
		return '';
	}

	if ( isset( $_POST[ $key ] ) && ! empty( $_POST[ $key ] ) ) {
		$captcha_code = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
		// phpcs:enable
	}

	/**
	 * Filter the CAPTCHA code retrieved from POST data.
	 *
	 * This filter allows developers to modify the CAPTCHA response code that was
	 * retrieved from the POST data and sanitized by the plugin. This can be used
	 * to modify or validate the CAPTCHA code before it is used for verification.
	 *
	 * @since 1.3.0
	 *
	 * @param string $captcha_code The sanitized CAPTCHA response code from POST data.
	 *                             This will be an empty string if the request method
	 *                             is not POST or if the CAPTCHA field was not set.
	 */
	$captcha_code = apply_filters( 'pwrcap_get_posted_captcha_code', $captcha_code );

	return $captcha_code;
}

/**
 * Get translatable text for Power Captcha messages.
 *
 * Applies a filter to allow custom messages in different contexts.
 *
 * @since 1.3.0
 *
 * @param string $msg     Message key to retrieve.
 * @param string $context Optional context to further specify the message.
 * @return string Translatable message string.
 */
function pwrcap_get_text( $msg, $context = null ) {
	switch ( $msg ) {
		case 'captcha-verification-failed':
			$text = esc_html__( 'Google reCAPTCHA verification failed.', 'power-captcha-recaptcha' );
			break;
		default:
			$text = '';
			break;
	}

	/**
	 * Filters the translatable message string used by Power Captcha.
	 *
	 * This allows customization of the message returned by `pwrcap_get_text()`
	 * based on a message key and optional context. Useful for integrations,
	 * custom UI messages, or localization enhancements.
	 *
	 * @since 1.3.0
	 *
	 * @param string $text    The translated text string associated with the message key.
	 * @param string $msg     Original message key for the text.
	 * @param string $context Optional context to refine or scope the message.
	 */
	$text = apply_filters( 'pwrcap_get_text', $text, $msg, $context );

	return $text;
}