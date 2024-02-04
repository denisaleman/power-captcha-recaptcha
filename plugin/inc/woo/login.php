<?php
/**
 * Captcha integration for WooCommerce login form
 *
 * @package PowerCaptchaReCaptcha/Woo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handle login form.
 *
 * @since 1.0.0
 *
 * @param WP_User|WP_Error $user     WP_User or WP_Error object.
 * @return WP_User|WP_Error Modified object.
 */
function pwrcap_handle_woo_login_form( $user ) {
	if ( true === apply_filters( 'pwrcap_prevent_handle_woo_login_form', false ) ) {
		return $user;
	}

	/**
	 * Make sure the WooCommerce login is being handled
	 */
	if ( ! isset( $_POST['woocommerce-login-nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return $user;
	}

	do_action( 'pwrcap_before_handle_captcha', __FUNCTION__ );

	if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['g-recaptcha-response'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( true !== pwrcap_validate_posted_captcha() ) {
			$user = new WP_Error( 'reCAPTCHA', '<strong>' . esc_html__( 'ERROR:', 'power-captcha-recaptcha' ) . '</strong> ' . esc_html__( 'Google reCAPTCHA verification failed.', 'power-captcha-recaptcha' ) );
		}
	} else {
		$user = new WP_Error( 'reCAPTCHA', '<strong>' . esc_html__( 'ERROR:', 'power-captcha-recaptcha' ) . '</strong> ' . esc_html__( 'Google reCAPTCHA verification failed.', 'power-captcha-recaptcha' ) );
	}

	return $user;
}

/**
 * Prevent WordPress login form captcha from unwanted handling.
 *
 * Since WooCommerce WooCommerce uses WordPress native's wp_signon() to handle auth,
 * enabling WordPress login form's captcha along with the WooCommerce login form's one
 * leads to unwanted double handling the latter.
 *
 * The function prevents that.
 *
 * @param bool $bool  Whether to perform handling or not.
 * @return bool Whether to perform handling or not.
 */
function pwrcap_prevent_login_form_handle( $bool ) {
	if ( isset( $_POST['woocommerce-login-nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return true;
	}
	return $bool;
}
add_filter( 'pwrcap_prevent_handle_login_form', 'pwrcap_prevent_login_form_handle', 10, 1 );

/**
 * Add login form handler
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_woo_login_form_add_handler() {
	$enabled = pwrcap_option( 'captchas', 'enable_woo_login' );
	if ( 0 === absint( $enabled ) ) {
		return;
	}

	add_filter( 'wp_authenticate_user', 'pwrcap_handle_woo_login_form', 10, 1 );
}
add_action( 'pwrcap_add_captcha_handler', 'pwrcap_woo_login_form_add_handler' );

/**
 * Add render function.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_wc_login_form_add_render() {
	$enabled = pwrcap_option( 'captchas', 'enable_woo_login' );
	if ( 0 === absint( $enabled ) ) {
		return;
	}

	if ( true !== pwrcap_is_setup_complete() ) {
		return;
	}

	$hook = 'woocommerce_login_form';

	$captcha_type = pwrcap_get_captcha_type();

	if ( 'v2cbx' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_wrapper' );
	} elseif ( 'v2inv' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_wrapper' );
	} elseif ( 'v3' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_input' );
	}
}
add_action( 'init', 'pwrcap_wc_login_form_add_render' );


/**
 * Register plugin option fields.
 *
 * @since 1.0.0
 */
function pwrcap_woo_login_form_register_enable_field() {
	add_settings_field(
		'enable_woo_login',
		esc_html__( 'Login Form', 'power-captcha-recaptcha' ),
		'pwrcap_field_enable_woo_login',
		'pwrcap_captchas_group',
		'pwrcap_woo_captcha_settings_section',
		array(
			'class' => apply_filters( 'pwrcap_woo_login_form_enable_field_class', '' ),
		)
	);
}
add_action( 'admin_init', 'pwrcap_woo_login_form_register_enable_field', 11 );

/**
 * Provide enable_woo_login setting field default value.
 *
 * @since 1.0.0
 *
 * @param array $defaults Array of default options values.
 * @return array Modified array of default options values.
 */
function pwrcap_field_enable_woo_login_default( $defaults ) {
	$defaults['enable_woo_login'] = 0;
	return $defaults;
}
add_filter( 'pwrcap_get_captchas_options_defaults', 'pwrcap_field_enable_woo_login_default', 10, 1 );

/**
 * Provide sanitized enable_woo_login option.
 *
 * @since 1.0.0
 *
 * @param array $options Array of options.
 * @return array $options Modified array of options.
 */
function pwrcap_sanitize_enable_woo_login( $options ) {
	$options['enable_woo_login'] = ( isset( $options['enable_woo_login'] ) && (bool) $options['enable_woo_login'] ) ? 1 : 0;
	return $options;
}
add_filter( 'pwrcap_sanitize_captchas_options', 'pwrcap_sanitize_enable_woo_login', 10, 1 );

/**
 * Render enable_woo_login field.
 *
 * @since 1.0.0
 */
function pwrcap_field_enable_woo_login() {
	$enable_woo_login = pwrcap_option( 'captchas', 'enable_woo_login' );
	?>
	<input type="checkbox" name="pwrcap_captchas_options[enable_woo_login]" id="enable_woo_login" value="1" <?php checked( 1, $enable_woo_login ); ?> />
	<?php
}
