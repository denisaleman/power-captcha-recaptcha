<?php
/**
 * Captcha integration for WooCommerce review form.
 *
 * @package PowerCaptchaReCaptcha/Woo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handle review form.
 *
 * @since 1.0.0
 *
 * @param array $commentdata Comment data.
 * @return array Modified comment data.
 */
function pwrcap_handle_woo_review_form( $commentdata ) {
	if ( true === apply_filters( 'pwrcap_prevent_handle_woo_review_form', false ) ) {
		return;
	}

	do_action( 'pwrcap_before_handle_captcha', __FUNCTION__ );

	// No need to check for loggedin user.
	if ( absint( $commentdata['user_ID'] ) > 0 ) {
		return $commentdata;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- not the function's responsibility
	// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated -- REQUEST_METHOD is safe, because it's set by the webserver software
	if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] && ! empty( $_POST['g-recaptcha-response'] ) ) {
		// phpcs:enable
		if ( true !== pwrcap_validate_posted_captcha() ) {
			wp_die(
				'<p><strong>' . esc_html__( 'ERROR:', 'power-captcha-recaptcha' ) . '</strong> ' . esc_html__( 'Google reCAPTCHA verification failed.', 'power-captcha-recaptcha' ) . '</p>',
				'reCAPTCHA',
				array(
					'response'  => 403,
					'back_link' => 1,
				)
			);
		}
	} else {
		wp_die(
			'<p><strong>' . esc_html__( 'ERROR:', 'power-captcha-recaptcha' ) . '</strong> ' . esc_html__( 'Google reCAPTCHA verification failed.', 'power-captcha-recaptcha' ) . '</p>',
			'reCAPTCHA',
			array(
				'response'  => 403,
				'back_link' => 1,
			)
		);
	}

	return $commentdata;
}

/**
 * Add review form handler.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_woo_review_form_add_handler() {
	$enabled = pwrcap_option( 'captchas', 'enable_woo_review' );
	if ( 0 === absint( $enabled ) ) {
		return;
	}

	add_filter( 'preprocess_comment', 'pwrcap_handle_woo_review_form', 10, 1 );
}
add_action( 'pwrcap_add_captcha_handler', 'pwrcap_woo_review_form_add_handler' );

/**
 * Add render function.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_woo_review_form_add_render() {
	$enabled = pwrcap_option( 'captchas', 'enable_woo_review' );
	if ( 0 === absint( $enabled ) ) {
		return;
	}

	if ( true !== pwrcap_is_setup_complete() ) {
		return;
	}

	$hook = 'comment_form_after_fields';

	$captcha_type = pwrcap_get_captcha_type();

	if ( 'v2cbx' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_wrapper' );
	} elseif ( 'v2inv' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_wrapper' );
	} elseif ( 'v3' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_input' );
	}
}
add_action( 'init', 'pwrcap_woo_review_form_add_render' );

/**
 * Prevent captcha from unwanted rendering for review form.
 *
 * Since WooCommerce uses WordPress native's comment_form() function to output the review form
 * enabling WordPress comment form captcha leads to unwanted rendering for the WooCommerce review form.
 *
 * The function prevents that.
 *
 * @param bool   $bool Whether render or not.
 * @param string $hook Hook the captcha is being rendering for.
 * @return bool  $bool Whether render or not.
 */
function pwrcap_prevent_render_woo_review_form( $bool, $hook ) {
	if ( 'comment_form_after_fields' === $hook && function_exists( 'is_product' ) && is_product() ) {
		$enabled = pwrcap_option( 'captchas', 'enable_woo_review' );
		if ( 0 === absint( $enabled ) ) {
			return true;
		}
	}
	return $bool;
}
add_filter( 'pwrcap_prevent_render_captcha', 'pwrcap_prevent_render_woo_review_form', 2, 10 );

/**
 * Register plugin option fields.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_woo_review_form_register_enable_field() {
	add_settings_field(
		'enable_woo_review',
		esc_html__( 'Comment Form', 'power-captcha-recaptcha' ),
		'pwrcap_field_enable_woo_review',
		'pwrcap_captchas_group',
		'pwrcap_woo_captcha_settings_section',
		array(
			'class' => apply_filters( 'pwrcap_woo_review_form_enable_field_class', '' ),
		)
	);
}
add_action( 'pwrcap_admin_init', 'pwrcap_woo_review_form_register_enable_field', 4 );

/**
 * Provide enable_woo_review setting field default value.
 *
 * @since 1.0.0
 *
 * @param array $defaults Array of default options values.
 * @return array Modified array of default options values.
 */
function pwrcap_field_enable_woo_review_default( $defaults ) {
	$defaults['enable_woo_review'] = 0;
	return $defaults;
}
add_filter( 'pwrcap_get_captchas_options_defaults', 'pwrcap_field_enable_woo_review_default', 10, 1 );

/**
 * Provide sanitized enable_woo_review option.
 *
 * @since 1.0.0
 *
 * @param array $options Array of options.
 * @return array $options Modified array of options.
 */
function pwrcap_sanitize_enable_woo_review( $options ) {
	$options['enable_woo_review'] = ( isset( $options['enable_woo_review'] ) && (bool) $options['enable_woo_review'] ) ? 1 : 0;
	return $options;
}
add_filter( 'pwrcap_sanitize_captchas_options', 'pwrcap_sanitize_enable_woo_review', 10, 1 );

/**
 * Render enable_woo_review_form field.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_field_enable_woo_review() {
	$enabled = pwrcap_option( 'captchas', 'enable_woo_review' );
	?>
	<input type="checkbox" name="pwrcap_captchas_options[enable_woo_review_form]" id="enable_woo_review_form" value="1" <?php checked( 1, $enabled ); ?> />
	<?php
}
