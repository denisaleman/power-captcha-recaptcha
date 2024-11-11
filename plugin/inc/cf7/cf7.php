<?php
/**
 * Power Captcha ReCaptcha integration for Contact Forms 7.
 *
 * @package PowerCaptchaReCaptcha/Cf7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add power_captcha_recaptcha form tag.
 *
 * @since 1.0.7
 *
 * @return void
 */
function pwrcap_add_form_tag_captcha() {
	wpcf7_add_form_tag(
		array( 'power_captcha_recaptcha*', 'power_captcha_recaptcha' ),
		'pwrcap_captcha_form_tag_handler',
		array( 'name-attr' => true )
	);
}
add_action( 'wpcf7_init', 'pwrcap_add_form_tag_captcha', 10, 0 );


/**
 * Add power captcha reCaptcha tag generator.
 *
 * @since 1.0.7
 *
 * @return void
 */
function pwrcap_add_tag_generator_captcha() {
	$tag_generator = WPCF7_TagGenerator::get_instance();

	$tag_generator->add(
		'power_captcha_recaptcha',
		__( 'power captcha reCaptcha', 'power-captcha-recaptcha' ),
		'pwrcap_tag_generator_captcha',
		array( 'version' => '2' )
	);
}
add_action( 'wpcf7_admin_init', 'pwrcap_add_tag_generator_captcha', 18, 0 );

/**
 * Handle power captcha reCaptcha tag in a form and provide html for it.
 *
 * @since 1.0.7
 *
 * @param object $tag The given tag.
 * @return string HTML code.
 */
function pwrcap_captcha_form_tag_handler( $tag ) {
	if ( empty( $tag->name ) ) {
		return '';
	}

	$validation_error = wpcf7_get_validation_error( $tag->name );

	$atts          = array();
	$atts['id']    = $tag->get_id_option();
	$atts['class'] = $tag->get_class_option( wpcf7_form_controls_class( $tag->type, 'wpcf7-form-control-wrap' ) );

	ob_start();
	do_action( 'pwrcap_cf7_render_captcha' );
	$captcha_html = ob_get_clean();

	$html = sprintf(
		'<div %2$s data-name="%1$s">%3$s</div>',
		sanitize_html_class( $tag->name ),
		wpcf7_format_atts( $atts ),
		$captcha_html,
		$validation_error
	);

	return $html;
}

/**
 * Add render function to the captcha form tag handler.
 *
 * @since 1.0.7
 *
 * @return void
 */
function pwrcap_cf7_form_handler_add_render() {
	if ( true !== pwrcap_is_setup_complete() ) {
		return;
	}

	$hook = 'pwrcap_cf7_render_captcha';

	$captcha_type = pwrcap_get_captcha_type();

	if ( 'v2cbx' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_wrapper' );
	} elseif ( 'v2inv' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_wrapper' );
	} elseif ( 'v3' === $captcha_type ) {
		add_action( $hook, 'pwrcap_render_captcha_input' );
	}
}
add_action( 'init', 'pwrcap_cf7_form_handler_add_render' );


/**
 * Tag generator captcha.
 *
 * @since 1.0.7
 *
 * @param WPCF7_ContactForm $contact_form ???.
 * @param string            $options      Tag options.
 * @return void
 */
function pwrcap_tag_generator_captcha( $contact_form, $options ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundBeforeLastUsed
	$is_setup_complete = pwrcap_is_setup_complete();

	if ( ! $is_setup_complete ) {
		/* translators: 1: link open, 2: link close */
		$message = sprintf( __( 'Complete setup to start using reCAPTCHA. %1$sComplete setup »%2$s ', 'power-captcha-recaptcha' ), '<a href="' . esc_url( admin_url( '/options-general.php?page=pwrcap-settings#tab-general' ) ) . '">', '</a>' );
		$link    = admin_url( 'options-general.php?page=pwrcap-settings' );
		$button  = __( 'Complete Setup', 'power-captcha-recaptcha' );
		?>
<header class="description-box">
	<p><?php echo wp_kses( $message, array( 'a' => array( 'href' => true ) ) ); ?></p>
	<p><a class="button" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $button ); ?></a></p>
</header>
		<?php
		return;
	}

	$field_types = array(
		'power_captcha_recaptcha' => array(
			'id'           => 'power_captcha_recaptcha',
			'display_name' => __( 'Power Captcha', 'power-captcha-recaptcha' ),
			'heading'      => __( 'Power Captcha ReCaptcha form-tag generator', 'power-captcha-recaptcha' ),
			'description'  => __( 'Generates a form-tag for a Power Captcha ReCaptcha field.', 'power-captcha-recaptcha' ),
		),
	);

	$tgg = new WPCF7_TagGeneratorGenerator( $options['content'] );
	?>
<header class="description-box">
	<h3><?php echo esc_html( $field_types['power_captcha_recaptcha']['heading'] ); ?></h3>
	<p><?php echo esc_html( $field_types['power_captcha_recaptcha']['description'] ); ?></p>
</header>

<div class="control-box">
	<div hidden><input checked type="checkbox" data-tag-part="type-suffix" value="*" /></div>
	<div hidden><input type="text" data-tag-part="basetype" value="<?php echo esc_attr( $field_types['power_captcha_recaptcha']['id'] ); ?>" /></div>
	<?php
		$tgg->print( 'field_name' );

		$tgg->print( 'class_attr' );

		$tgg->print( 'id_attr' );
	?>
</div>

<footer class="insert-box">
	<?php
		$tgg->print( 'insert_box_content' );
	?>
</footer>
	<?php
}

/**
 * Validate power_captcha_recaptcha field.
 *
 * @since 1.0.7
 *
 * @param WPCF7_Validation $validation Server-side user input validation manager.
 * @param object           $tag        Form tag.
 * @return WPCF7_Validation
 */
function pwrcap_captcha_validation_filter( $validation, $tag ) {
	if ( 'power_captcha_recaptcha' === $tag->basetype ) {
		if ( $tag->is_required() ) {
			$rest_field_are_valid = $validation->is_valid();
			$captcha_code         = pwrcap_get_posted_captcha_code();

			if ( empty( $captcha_code ) ) {
				$validation->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
				return $validation;
			}

			if ( $rest_field_are_valid ) {
				$is_valid = pwrcap_is_valid_captcha_code( $captcha_code );
				if ( ! $is_valid ) {
					$validation->invalidate( $tag, __( 'Google reCAPTCHA verification failed.', 'power-captcha-recaptcha' ) );
				}
			}
		}
	}
	return $validation;
}
add_filter( 'wpcf7_validate_power_captcha_recaptcha', 'pwrcap_captcha_validation_filter', 10, 2 );
add_filter( 'wpcf7_validate_power_captcha_recaptcha*', 'pwrcap_captcha_validation_filter', 10, 2 );
