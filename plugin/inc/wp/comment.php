<?php
/**
 * Captcha integration for WordPress comment form.
 *
 * @package PowerCaptchaReCaptcha/Wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handle comment form.
 *
 * @since 1.0.0
 *
 * @param array $commentdata Comment data.
 * @return array Modified comment data.
 */
function pwrcap_handle_comment_form( $commentdata ) {
	if ( true === apply_filters( 'pwrcap_prevent_handle_comment_form', false ) ) {
		return $commentdata;
	}

	do_action( 'pwrcap_before_handle_captcha', __FUNCTION__ );

	if ( absint( $commentdata['user_ID'] ) > 0 ) {
		return $commentdata;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- not the function's responsibility
	// phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	if ( 'POST' === $_SERVER['REQUEST_METHOD'] && ! empty( $_POST['g-recaptcha-response'] ) ) {
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
 * Add comment form handler.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_comment_form_add_handler() {
	$enabled = pwrcap_option( 'captchas', 'enable_comment' );
	if ( 0 === absint( $enabled ) ) {
		return;
	}

	add_filter( 'preprocess_comment', 'pwrcap_handle_comment_form', 10, 1 );
}
add_action( 'pwrcap_add_captcha_handler', 'pwrcap_comment_form_add_handler' );

/**
 * Add render function.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_comment_form_add_render() {
	$enabled = pwrcap_option( 'captchas', 'enable_comment' );
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
add_action( 'init', 'pwrcap_comment_form_add_render' );

/**
 * Register plugin option fields.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_comment_form_register_enable_field() {
	add_settings_field(
		'enable_comment',
		esc_html__( 'Comment Form', 'power-captcha-recaptcha' ),
		'pwrcap_field_enable_comment',
		'pwrcap_captchas_group',
		'pwrcap_wp_captcha_settings_section',
		array(
			'class' => apply_filters( 'pwrcap_comment_form_enable_field_class', '' ),
		)
	);
}
add_action( 'pwrcap_admin_init', 'pwrcap_comment_form_register_enable_field', 4 );

/**
 * Provide enable_comment setting field default value.
 *
 * @since 1.0.0
 *
 * @param array $defaults Array of default options values.
 * @return array Modified array of default options values.
 */
function pwrcap_field_enable_comment_default( $defaults ) {
	$defaults['enable_comment'] = 0;
	return $defaults;
}
add_filter( 'pwrcap_get_captchas_options_defaults', 'pwrcap_field_enable_comment_default', 10, 1 );

/**
 * Provide sanitized enable_comment option.
 *
 * @since 1.0.0
 *
 * @param array $options Array of options.
 * @return array $options Modified array of options.
 */
function pwrcap_sanitize_enable_comment( $options ) {
	$options['enable_comment'] = ( isset( $options['enable_comment'] ) && (bool) $options['enable_comment'] ) ? 1 : 0;
	return $options;
}
add_filter( 'pwrcap_sanitize_captchas_options', 'pwrcap_sanitize_enable_comment', 10, 1 );

/**
 * Render enable_comment field.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_field_enable_comment() {
	$enabled = pwrcap_option( 'captchas', 'enable_comment' );
	?>
	<input type="checkbox" name="pwrcap_captchas_options[enable_comment]" id="enable_comment" value="1" <?php checked( 1, $enabled ); ?> />
	<?php
}
