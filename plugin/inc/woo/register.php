<?php
/**
 * Captcha integration for WooCommerce register form.
 *
 * @package PowerCaptchaReCaptcha/Woo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Process WooCommerce register form.
 *
 * @since 1.0.0
 *
 * @param string   $username Username.
 * @param string   $email Email.
 * @param WP_Error $errors A WP_Error object.
 */
function pwrcap_handle_woo_register_form( $username, $email, $errors ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed
	if ( true === apply_filters( 'pwrcap_prevent_handle_woo_register_form', false ) ) {
		return;
	}

	do_action( 'pwrcap_before_handle_captcha', __FUNCTION__ );

	if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['g-recaptcha-response'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( true !== advanced_google_recaptcha_validate_posted_captcha() ) {
			$errors->add( 'reCAPTCHA', esc_html__( 'Google reCAPTCHA verification failed.', 'power-captcha-recaptcha' ) );
		}
	} else {
		$errors->add( 'reCAPTCHA', esc_html__( 'Google reCAPTCHA verification failed.', 'power-captcha-recaptcha' ) );
	}
}

/**
 * Add register form handler.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_woo_register_form_add_handler() {
	$enabled = pwrcap_option( 'captchas', 'enable_woo_register' );
	if ( 0 === absint( $enabled ) ) {
		return;
	}

	add_filter( 'woocommerce_register_post', 'pwrcap_handle_woo_register_form', 10, 3 );
	add_action( 'init',
		function() {
			if ( wp_doing_ajax() ) {
				remove_action( 'woocommerce_register_post', 'pwrcap_handle_woo_register_form', 10, 3 );
			}
		},
		11
	);
}
add_action( 'pwrcap_add_captcha_handler', 'pwrcap_woo_register_form_add_handler' );

/**
 * Add render function.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_woo_register_form_add_render() {
	$enabled = pwrcap_option( 'captchas', 'enable_woo_register' );
	if ( 0 === absint( $enabled ) ) {
		return;
	}

	if ( true !== pwrcap_is_setup_complete() ) {
		return;
	}

	$hook = 'woocommerce_register_form';

	$captcha_type = pwrcap_get_captcha_type();

	if ( 'v2cbx' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_wrapper' );
	} elseif ( 'v2inv' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_wrapper' );
	} elseif ( 'v3' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_input' );
	}
}
add_action( 'init', 'pwrcap_woo_register_form_add_render' );

/**
 * Register register form captcha option field.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_woo_register_form_register_enable_field() {
	add_settings_field(
		'enable_woo_register',
		esc_html__( 'Register Form', 'power-captcha-recaptcha' ),
		'pwrcap_field_enable_woo_register',
		'pwrcap_captchas_group',
		'pwrcap_woo_captcha_settings_section',
		array(
			'class' => apply_filters( 'pwrcap_woo_register_form_enable_field_class', '' ),
		)
	);
}
add_action( 'admin_init', 'pwrcap_woo_register_form_register_enable_field', 12 );

/**
 * Provide enable_woo_register setting field default value.
 *
 * @since 1.0.0
 *
 * @param array $defaults Array of default options values.
 * @return array Modified array of default options values.
 */
function pwrcap_field_enable_woo_register_default( $defaults ) {
	$defaults['enable_woo_register'] = 0;
	return $defaults;
}
add_filter( 'pwrcap_get_captchas_options_defaults', 'pwrcap_field_enable_woo_register_default', 10, 1 );

/**
 * Provide sanitized enable_woo_register option.
 *
 * @since 1.0.0
 *
 * @param array $options Array of options.
 * @return array $options Modified array of options.
 */
function pwrcap_sanitize_enable_woo_register( $options ) {
	$options['enable_woo_register'] = ( isset( $options['enable_woo_register'] ) && (bool) $options['enable_woo_register'] ) ? 1 : 0;
	return $options;
}
add_filter( 'pwrcap_sanitize_captchas_options', 'pwrcap_sanitize_enable_woo_register', 10, 1 );

/**
 * Register captcha option field.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_field_enable_woo_register() {
	$enable_woo_register = pwrcap_option( 'captchas', 'enable_woo_register' );
	?>
	<input type="checkbox" name="pwrcap_captchas_options[enable_woo_register]" id="enable_woo_register" value="1" <?php checked( 1, $enable_woo_register ); ?> />
	<?php
}
