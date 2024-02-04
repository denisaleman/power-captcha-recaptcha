<?php
/**
 * Captcha integration for WooCommerce reset password form.
 *
 * @package PowerCaptchaReCaptcha/Woo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handle reset password form.
 *
 * @since 1.0.0
 *
 * @param WP_User|WP_Error $errors     WP_User or WP_Error object.
 * @return void
 */
function pwrcap_handle_woo_resetpassword_form( $errors ) {
	if ( true === apply_filters( 'pwrcap_prevent_handle_woo_resetpassword_form', false ) ) {
		return;
	}

	/**
	 * Make sure the WooCommerce reset password form is being handled
	 */
	if ( ! isset( $_POST['woocommerce-reset-password-nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	do_action( 'pwrcap_before_handle_captcha', __FUNCTION__ );

	if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['g-recaptcha-response'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( true !== pwrcap_validate_posted_captcha() ) {
			$errors->add( 'reCAPTCHA', esc_html__( 'Google reCAPTCHA verification failed.', 'power-captcha-recaptcha' ) );
		}
	} else {
		$errors->add( 'reCAPTCHA', esc_html__( 'Google reCAPTCHA verification failed.', 'power-captcha-recaptcha' ) );
	}
}

/**
 * Prevent WordPress reset password form captcha from unwanted handling.
 *
 * Since validate_password_reset hook is used in both WooCommerce's and WordPress reset password
 * mechanism for hanler function to hook onto, we must prevent unwanted double handling.
 *
 * The function prevents that.
 *
 * @param bool $bool  Whether to perform handling or not.
 * @return bool Whether to perform handling or not.
 */
function pwrcap_prevent_handle_resetpassword_form( $bool ) {
	if ( isset( $_POST['woocommerce-reset-password-nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return true;
	}
	return $bool;
}
add_filter( 'pwrcap_prevent_handle_resetpassword_form', 'pwrcap_prevent_handle_resetpassword_form', 10, 1 );

/**
 * Add login form handler.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_woo_resetpassword_form_add_handler() {
	$enabled = pwrcap_option( 'captchas', 'enable_woo_resetpassword' );
	if ( 0 === absint( $enabled ) ) {
		return;
	}

	add_action( 'validate_password_reset', 'pwrcap_handle_woo_resetpassword_form', 10, 1 );
}
add_action( 'pwrcap_add_captcha_handler', 'pwrcap_woo_resetpassword_form_add_handler' );

/**
 * Add render function.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_woo_resetpassword_form_add_render() {
	$enabled = pwrcap_option( 'captchas', 'enable_woo_resetpassword' );
	if ( 0 === absint( $enabled ) ) {
		return;
	}

	if ( true !== pwrcap_is_setup_complete() ) {
		return;
	}

	$hook = 'woocommerce_resetpassword_form';

	$captcha_type = pwrcap_get_captcha_type();

	if ( 'v2cbx' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_wrapper' );
	} elseif ( 'v2inv' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_wrapper' );
	} elseif ( 'v3' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_input' );
	}
}
add_action( 'init', 'pwrcap_woo_resetpassword_form_add_render' );

/**
 * Register captcha option field.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_woo_resetpassword_form_register_enable_field() {
	add_settings_field(
		'enable_woo_resetpassword',
		esc_html__( 'Reset Password Form', 'power-captcha-recaptcha' ),
		'pwrcap_field_enable_woo_resetpassword_form',
		'pwrcap_captchas_group',
		'pwrcap_woo_captcha_settings_section',
		array(
			'class' => apply_filters( 'pwrcap_woo_resetpassword_form_enable_field_class', '' ),
		)
	);
}
add_action( 'admin_init', 'pwrcap_woo_resetpassword_form_register_enable_field', 14 );

/**
 * Provide enable_woo_resetpassword setting field default value.
 *
 * @since 1.0.0
 *
 * @param array $defaults Array of default options values.
 * @return array Modified array of default options values.
 */
function pwrcap_field_enable_woo_resetpassword_default( $defaults ) {
	$defaults['enable_woo_resetpassword'] = 0;
	return $defaults;
}
add_filter( 'pwrcap_get_captchas_options_defaults', 'pwrcap_field_enable_woo_resetpassword_default', 10, 1 );

/**
 * Provide sanitized enable_woo_resetpassword option.
 *
 * @since 1.0.0
 *
 * @param array $options Array of options.
 * @return array $options Modified array of options.
 */
function pwrcap_sanitize_enable_woo_resetpassword( $options ) {
	$options['enable_woo_resetpassword'] = ( isset( $options['enable_woo_resetpassword'] ) && (bool) $options['enable_woo_resetpassword'] ) ? 1 : 0;
	return $options;
}
add_filter( 'pwrcap_sanitize_captchas_options', 'pwrcap_sanitize_enable_woo_resetpassword', 10, 1 );

/**
 * Render option field.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_field_enable_woo_resetpassword_form() {
	$enabled = pwrcap_option( 'captchas', 'enable_woo_resetpassword' );
	?>
	<input type="checkbox" name="pwrcap_captchas_options[enable_woo_resetpassword]" id="enable_woo_resetpassword" value="1" <?php checked( 1, $enabled ); ?> />
	<?php
}
