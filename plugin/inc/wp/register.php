<?php
/**
 * Captcha integration for register form.
 *
 * @package PowerCaptchaReCaptcha/Wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handle register form.
 *
 * @since 1.0.0
 *
 * @param WP_Error $errors A WP_Error object.
 * @return WP_Error Modified error object.
 */
function pwrcap_handle_register_form( $errors ) {
	if ( true === apply_filters( 'pwrcap_prevent_handle_register_form', false ) ) {
		return $errors;
	}

	do_action( 'pwrcap_before_handle_captcha', __FUNCTION__ );

	if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['g-recaptcha-response'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( true !== pwrcap_validate_posted_captcha() ) {
			$errors = new WP_Error( 'reCAPTCHA', '<strong>' . esc_html__( 'ERROR:', 'power-captcha-recaptcha' ) . '</strong> ' . esc_html__( 'Google reCAPTCHA verification failed.', 'power-captcha-recaptcha' ) );
		}
	} else {
		$errors = new WP_Error( 'reCAPTCHA', '<strong>' . esc_html__( 'ERROR:', 'power-captcha-recaptcha' ) . '</strong> ' . esc_html__( 'Google reCAPTCHA verification failed.', 'power-captcha-recaptcha' ) );
	}

	return $errors;
}

/**
 * Add register form handler.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_register_form_add_handler() {
	$enabled = pwrcap_option( 'captchas', 'enable_register' );
	if ( 0 === absint( $enabled ) ) {
		return;
	}

	add_action(
		'login_form_register',
		function() {
			add_filter( 'registration_errors', 'pwrcap_handle_register_form' );
		}
	);
}
add_action( 'pwrcap_add_captcha_handler', 'pwrcap_register_form_add_handler' );

/**
 * Add render function.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_register_form_add_render() {
	$enabled = pwrcap_option( 'captchas', 'enable_register' );
	if ( 0 === absint( $enabled ) ) {
		return;
	}

	if ( true !== pwrcap_is_setup_complete() ) {
		return;
	}

	$hook = 'register_form';

	$captcha_type = pwrcap_get_captcha_type();

	if ( 'v2cbx' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_wrapper' );
	} elseif ( 'v2inv' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_wrapper' );
	} elseif ( 'v3' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_input' );
	}
}
add_action( 'init', 'pwrcap_register_form_add_render' );

/**
 * Register plugin option fields.
 *
 * @since 1.0.0
 */
function pwrcap_register_form_register_enable_field() {
	add_settings_field(
		'enable_register',
		esc_html__( 'Register Form', 'power-captcha-recaptcha' ),
		'pwrcap_register_form_render_enable_field',
		'pwrcap_captchas_group',
		'pwrcap_wp_captcha_settings_section',
		array(
			'class' => apply_filters( 'pwrcap_register_form_enable_field_class', '' ),
		)
	);
}
add_action( 'pwrcap_admin_init', 'pwrcap_register_form_register_enable_field', 2 );

/**
 * Provide enable_register setting field default value.
 *
 * @since 1.0.0
 *
 * @param array $defaults Array of default options values.
 * @return array Modified array of default options values.
 */
function pwrcap_field_enable_register_default( $defaults ) {
	$defaults['enable_register'] = 0;
	return $defaults;
}
add_filter( 'pwrcap_get_captchas_options_defaults', 'pwrcap_field_enable_register_default', 10, 1 );

/**
 * Provide sanitized enable_register option.
 *
 * @since 1.0.0
 *
 * @param array $options Array of options.
 * @return array $options Modified array of options.
 */
function pwrcap_sanitize_enable_register( $options ) {
	$options['enable_register'] = ( isset( $options['enable_register'] ) && (bool) $options['enable_register'] ) ? 1 : 0;
	return $options;
}
add_filter( 'pwrcap_sanitize_captchas_options', 'pwrcap_sanitize_enable_register', 10, 1 );

/**
 * Register enable_register field.
 *
 * @since 1.0.0
 */
function pwrcap_register_form_render_enable_field() {
	$enabled = pwrcap_option( 'captchas', 'enable_register' );
	?>
	<input type="checkbox" name="pwrcap_captchas_options[enable_register]" id="enable_register" value="1" <?php checked( 1, $enabled ); ?> />
	<?php
}
