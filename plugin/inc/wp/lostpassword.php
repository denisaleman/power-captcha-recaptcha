<?php
/**
 * Captcha integration for Lost Password form.
 *
 * @package PowerCaptchaReCaptcha/Wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handle lost password form.
 *
 * @since 1.0.0
 *
 * @param WP_Error $errors A WP_Error object.
 */
function pwrcap_handle_lostpassword_form( $errors ) {
	if ( true === apply_filters( 'pwrcap_prevent_handle_lostpassword_form', false ) ) {
		return;
	}

	do_action( 'pwrcap_before_handle_captcha', __FUNCTION__ );

	if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['g-recaptcha-response'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( true !== pwrcap_validate_posted_captcha() ) {
			$errors->add( 'reCAPTCHA', '<strong>' . esc_html__( 'ERROR:', 'power-captcha-recaptcha' ) . '</strong> ' . esc_html__( 'Google reCAPTCHA verification failed.', 'power-captcha-recaptcha' ) );
		}
	} else {
		$errors->add( 'reCAPTCHA', '<strong>' . esc_html__( 'ERROR:', 'power-captcha-recaptcha' ) . '</strong> ' . esc_html__( 'Google reCAPTCHA verification failed.', 'power-captcha-recaptcha' ) );
	}
}

/**
 * Add lostpassword form handler.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_lostpassword_form_add_handler() {
	$enabled = pwrcap_option( 'captchas', 'enable_lostpassword' );
	if ( 0 === absint( $enabled ) ) {
		return;
	}

	add_action( 'lostpassword_post', 'pwrcap_handle_lostpassword_form', 10, 1 );
}
add_action( 'pwrcap_add_captcha_handler', 'pwrcap_lostpassword_form_add_handler' );

/**
 * Add render function.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_lostpassword_form_add_render() {
	$enabled = pwrcap_option( 'captchas', 'enable_lostpassword' );
	if ( 0 === absint( $enabled ) ) {
		return;
	}

	if ( true !== pwrcap_is_setup_complete() ) {
		return;
	}

	$hook = 'lostpassword_form';

	$captcha_type = pwrcap_get_captcha_type();

	if ( 'v2cbx' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_wrapper' );
	} elseif ( 'v2inv' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_wrapper' );
	} elseif ( 'v3' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_input' );
	}

	/**
	 * Fires after CAPTCHA render function is hooked to the WordPress lost password form.
	 *
	 * This action is triggered when the plugin conditionally attaches a CAPTCHA render
	 * function to the WordPress lost password form via the `lostpassword_form` action hook.
	 *
	 * The hook is only added if:
	 * - CAPTCHA for the lost password form is enabled via plugin settings.
	 * - The CAPTCHA setup process has been successfully completed.
	 *
	 * Possible `$captcha_type` values:
	 * - 'v2cbx' — reCAPTCHA v2 Checkbox
	 * - 'v2inv' — reCAPTCHA v2 Invisible
	 * - 'v3'    — reCAPTCHA v3 (score-based)
	 *
	 * @since 1.3.0
	 *
	 * @param string $captcha_type Type of CAPTCHA being used.
	 * @param string $hook         The action hook where CAPTCHA was attached (typically 'lostpassword_form').
	 */
	do_action( 'pwrcap_lostpassword_form_add_render', $captcha_type, $hook );
}
add_action( 'init', 'pwrcap_lostpassword_form_add_render' );

/**
 * Register plugin option fields.
 *
 * @since 1.0.0
 */
function pwrcap_lostpassword_form_register_enable_field() {
	add_settings_field(
		'enable_lostpassword',
		esc_html__( 'Lost Password Form', 'power-captcha-recaptcha' ),
		'pwrcap_lostpassword_form_render_enable_field',
		'pwrcap_captchas_group',
		'pwrcap_wp_captcha_settings_section',
		array(
			'class' => apply_filters( 'pwrcap_lostpassword_form_enable_field_class', '' ),
		)
	);
}
add_action( 'pwrcap_admin_init', 'pwrcap_lostpassword_form_register_enable_field', 3 );

/**
 * Provide enable_lostpassword setting field default value.
 *
 * @since 1.0.0
 *
 * @param array $defaults Array of default options values.
 * @return array Modified array of default options values.
 */
function pwrcap_field_enable_lostpassword_default( $defaults ) {
	$defaults['enable_lostpassword'] = 0;
	return $defaults;
}
add_filter( 'pwrcap_get_captchas_options_defaults', 'pwrcap_field_enable_lostpassword_default', 10, 1 );

/**
 * Provide sanitized enable_lostpassword option.
 *
 * @since 1.0.0
 *
 * @param array $options Array of options.
 * @return array $options Modified array of options.
 */
function pwrcap_sanitize_enable_lostpassword( $options ) {
	$options['enable_lostpassword'] = ( isset( $options['enable_lostpassword'] ) && (bool) $options['enable_lostpassword'] ) ? 1 : 0;
	return $options;
}
add_filter( 'pwrcap_sanitize_captchas_options', 'pwrcap_sanitize_enable_lostpassword', 10, 1 );

/**
 * Register enable_lostpassword field.
 *
 * @since 1.0.0
 */
function pwrcap_lostpassword_form_render_enable_field() {
	$enabled = pwrcap_option( 'captchas', 'enable_lostpassword' );
	?>
	<input type="checkbox" name="pwrcap_captchas_options[enable_lostpassword]" id="enable_lostpassword" value="1" <?php checked( 1, $enabled ); ?> />
	<?php
}
