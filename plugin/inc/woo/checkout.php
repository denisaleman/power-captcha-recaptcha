<?php
/**
 * Captcha integration for WooCommerce checkout form.
 *
 * @package PowerCaptchaReCaptcha/Woo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handle checkout form.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_handle_woo_checkout_form() {
	if ( true === apply_filters( 'pwrcap_prevent_handle_woo_checkout_form', false ) ) {
		return;
	}

	do_action( 'pwrcap_before_handle_captcha', __FUNCTION__ );

	if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['g-recaptcha-response'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( true !== pwrcap_validate_posted_captcha() ) {
			wc_add_notice( esc_html__( 'Google reCAPTCHA verification failed.', 'power-captcha-recaptcha' ), 'error' );
		}
	} else {
		wc_add_notice( esc_html__( 'Google reCAPTCHA verification failed.', 'power-captcha-recaptcha' ), 'error' );
	}
}

/**
 * Add checkout form handler.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_woo_checkout_form_add_handler() {
	$enabled = pwrcap_option( 'captchas', 'enable_woo_checkout' );
	if ( 0 === absint( $enabled ) ) {
		return;
	}

	add_filter( 'woocommerce_checkout_process', 'pwrcap_handle_woo_checkout_form', 10, 1 );
}
add_action( 'pwrcap_add_captcha_handler', 'pwrcap_woo_checkout_form_add_handler' );

/**
 * Add render function.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_woo_checkout_form_add_render() {
	$enabled = pwrcap_option( 'captchas', 'enable_woo_checkout' );
	if ( 0 === absint( $enabled ) ) {
		return;
	}

	if ( true !== pwrcap_is_setup_complete() ) {
		return;
	}

	$hook = 'woocommerce_checkout_after_customer_details';

	$captcha_type = pwrcap_get_captcha_type();

	if ( 'v2cbx' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_wrapper' );
	} elseif ( 'v2inv' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_wrapper' );
	} elseif ( 'v3' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_input' );
	}
}
add_action( 'init', 'pwrcap_woo_checkout_form_add_render' );


/**
 * Register captcha option field.
 *
 * @return void
 *
 * @since 1.0.0
 */
function pwrcap_woo_checkout_form_register_enable_field() {
	add_settings_field(
		'enable_woo_checkout',
		esc_html__( 'Checkout Form', 'power-captcha-recaptcha' ),
		'pwrcap_field_enable_woo_checkout',
		'pwrcap_captchas_group',
		'pwrcap_woo_captcha_settings_section',
		array(
			'class' => apply_filters( 'pwrcap_woo_checkout_form_enable_field_class', '' ),
		)
	);
}
add_action( 'pwrcap_admin_init', 'pwrcap_woo_checkout_form_register_enable_field', 15 );

/**
 * Provide enable_woo_checkout setting field default value.
 *
 * @since 1.0.0
 *
 * @param array $defaults Array of default options values.
 * @return array Modified array of default options values.
 */
function pwrcap_field_enable_woo_checkout_default( $defaults ) {
	$defaults['enable_woo_checkout'] = 0;
	return $defaults;
}
add_filter( 'pwrcap_get_captchas_options_defaults', 'pwrcap_field_enable_woo_checkout_default', 10, 1 );

/**
 * Provide sanitized enable_woo_checkout option.
 *
 * @since 1.0.0
 *
 * @param array $options Array of options.
 * @return array $options Modified array of options.
 */
function pwrcap_sanitize_enable_woo_checkout( $options ) {
	$options['enable_woo_checkout'] = ( isset( $options['enable_woo_checkout'] ) && (bool) $options['enable_woo_checkout'] ) ? 1 : 0;
	return $options;
}
add_filter( 'pwrcap_sanitize_captchas_options', 'pwrcap_sanitize_enable_woo_checkout', 10, 1 );

/**
 * Render enable_woo_checkout field.
 *
 * @return void
 *
 * @since 1.0.0
 */
function pwrcap_field_enable_woo_checkout() {
	$enable_woo_checkout = pwrcap_option( 'captchas', 'enable_woo_checkout' );
	?>
	<input type="checkbox" name="pwrcap_captchas_options[enable_woo_checkout]" id="enable_woo_checkout" value="1" <?php checked( 1, $enable_woo_checkout ); ?> />
	<?php
}
