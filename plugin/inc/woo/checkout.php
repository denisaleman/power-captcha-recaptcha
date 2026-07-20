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

		/**
	 * Fires after CAPTCHA render function is hooked to the WooCommerce checkout form.
	 *
	 * This action is triggered when the plugin conditionally attaches a CAPTCHA render
	 * function to the WooCommerce checkout page. The CAPTCHA is injected using the
	 * `woocommerce_checkout_after_customer_details` hook (subject to change as noted).
	 *
	 * The hook is only added if:
	 * - CAPTCHA for WooCommerce checkout is enabled via plugin options.
	 * - The CAPTCHA setup process has been completed.
	 *
	 * Note: For better placement, consider using 'woocommerce_review_order_before_submit'
	 * in the future, as indicated in the inline comment.
	 *
	 * Possible `$captcha_type` values:
	 * - 'v2cbx' — reCAPTCHA v2 Checkbox
	 * - 'v2inv' — reCAPTCHA v2 Invisible
	 * - 'v3'    — reCAPTCHA v3 (score-based)
	 *
	 * @since 1.3.0
	 *
	 * @param string $captcha_type Type of CAPTCHA being used.
	 * @param string $hook         The hook name to which the CAPTCHA was attached.
	 */
	do_action( 'pwrcap_woo_checkout_form_add_render', $captcha_type, $hook );
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
