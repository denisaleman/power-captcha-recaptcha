<?php
/**
 * Captcha integration for reset password form.
 *
 * @package PowerCaptchaReCaptcha/Wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Process reset password form.
 *
 * @since 1.0.0
 *
 * @param WP_Error $errors WP_Error object.
 */
function pwrcap_handle_resetpassword_form( $errors ) {
	if ( true === apply_filters( 'pwrcap_prevent_handle_resetpassword_form', false ) ) {
		return;
	}

	/**
	 * Make sure the WordPress reset password form is being handled
	 */
	if ( ! isset( $_POST['rp_key'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	do_action( 'pwrcap_before_handle_captcha', __FUNCTION__ );

	if ( isset( $_POST['g-recaptcha-response'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( true !== pwrcap_validate_posted_captcha() ) {
			$errors->add( 'reCAPTCHA', esc_html__( 'Google reCAPTCHA verification failed.', 'power-captcha-recaptcha' ) );
		}
	}
}

/**
 * Add reset password form handler.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_resetpassword_form_add_handler() {
	$enabled = pwrcap_option( 'captchas', 'enable_resetpassword' );
	if ( 0 === absint( $enabled ) ) {
		return;
	}

	add_action( 'validate_password_reset', 'pwrcap_handle_resetpassword_form', 10, 1 );
}
add_action( 'pwrcap_add_captcha_handler', 'pwrcap_resetpassword_form_add_handler' );

/**
 * Add render function.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_resetpassword_form_add_render() {
	$enabled = pwrcap_option( 'captchas', 'enable_resetpassword' );
	if ( 0 === absint( $enabled ) ) {
		return;
	}

	if ( true !== pwrcap_is_setup_complete() ) {
		return;
	}

	$hook = 'resetpass_form';

	$captcha_type = pwrcap_get_captcha_type();

	if ( 'v2cbx' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_wrapper' );
	} elseif ( 'v2inv' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_wrapper' );
	} elseif ( 'v3' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_input' );
	}
}
add_action( 'init', 'pwrcap_resetpassword_form_add_render' );

/**
 * Register plugin option fields.
 *
 * @since 1.0.0
 */
function pwrcap_resetpassword_form_register_enable_field() {
	add_settings_field(
		'enable_resetpassword',
		esc_html__( 'Reset Password Form', 'power-captcha-recaptcha' ),
		'pwrcap_resetpassword_form_render_enable_field',
		'pwrcap_captchas_group',
		'pwrcap_wp_captcha_settings_section',
		array(
			'class' => apply_filters( 'pwrcap_resetpassword_form_enable_field_class', '' ),
		)
	);
}
add_action( 'pwrcap_admin_init', 'pwrcap_resetpassword_form_register_enable_field', 3 );

/**
 * Provide enable_resetpassword setting field default value.
 *
 * @since 1.0.0
 *
 * @param array $defaults Array of default options values.
 * @return array Modified array of default options values.
 */
function pwrcap_field_enable_resetpassword_default( $defaults ) {
	$defaults['enable_resetpassword'] = 0;
	return $defaults;
}
add_filter( 'pwrcap_get_captchas_options_defaults', 'pwrcap_field_enable_resetpassword_default', 10, 1 );

/**
 * Provide sanitized enable_resetpassword option.
 *
 * @since 1.0.0
 *
 * @param array $options Array of options.
 * @return array $options Modified array of options.
 */
function pwrcap_sanitize_enable_resetpassword( $options ) {
	$options['enable_resetpassword'] = ( isset( $options['enable_resetpassword'] ) && (bool) $options['enable_resetpassword'] ) ? 1 : 0;
	return $options;
}
add_filter( 'pwrcap_sanitize_captchas_options', 'pwrcap_sanitize_enable_resetpassword', 10, 1 );

/**
 * Register enable_lostpassword field.
 *
 * @since 1.0.0
 */
function pwrcap_resetpassword_form_render_enable_field() {
	$enabled = pwrcap_option( 'captchas', 'enable_resetpassword' );
	?>
	<input type="checkbox" name="pwrcap_captchas_options[enable_resetpassword]" id="enable_resetpassword" value="1" <?php checked( 1, $enabled ); ?> />
	<?php
}
