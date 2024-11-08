<?php
/**
 * Power Captcha ReCaptcha common functionality for Contact Forms 7 integration.
 *
 * @package PowerCaptchaReCaptcha/Cf7
 */

/**
 * Add Contact Forms 7 captcha settings section.
 *
 * @since 1.0.7
 *
 * @return void
 */
function pwrcap_add_cf7_captcha_setting_section() {
	global $wp_settings_sections;
	$page = 'pwrcap_captchas_group';
	$id   = 'pwrcap_cf7_captcha_settings_section';
	if ( ! isset( $wp_settings_sections[ $page ][ $id ] ) ) {
		add_settings_section(
			$id,
			esc_html__( 'Contact Forms 7', 'power-captcha-recaptcha' ),
			function () {
				do_action( 'pwrcap_do_cf7_captchas_section' );
			},
			$page,
			array(
				'before_section' => apply_filters( 'pwrcap_cf7_captchas_before_section', '' ),
				'after_section'  => apply_filters( 'pwrcap_cf7_captchas_after_section', '' ),
				'section_class'  => apply_filters( 'pwrcap_cf7_captchas_section_class', null ),
			)
		);
	}
}
add_action( 'pwrcap_admin_init', 'pwrcap_add_cf7_captcha_setting_section', 10 );

/**
 * Add Contact Form 7 settings section explanation text.
 *
 * @since 1.0.7
 *
 * @return void
 */
function pwrcap_add_cf7_captcha_setting_section_explanation() {
	if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
		?>
		<p>
			<?php
				$cf7section_url = menu_page_url( 'wpcf7', false );
				printf(
					/* translators: 1: link open, 2: link close */
					__( 'To use Power Captcha ReCAPTCHA in Contact Form 7, go to the %1$sContact Forms 7 section%2$s.', 'power-captcha-recaptcha' ),
					empty( $cf7section_url ) ? '' : sprintf( '<a href="%s">', esc_url( $cf7section_url ) ),
					empty( $cf7section_url ) ? '' : '</a>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- no XSS
				);
			?>
			<br />
			<?php
				esc_html_e( 'Edit each form where you want to add Power Captcha ReCAPTCHA, and insert the field as you would with any other form field.', 'power-captcha-recaptcha' )
			?>
		</p>
		<?php
	} else {
		?>
		<p><?php esc_html_e( 'The Contact Form 7 plugin is not installed or is deactivated.', 'power-captcha-recaptcha' ); ?></p>
		<?php
	}
	?>
	<hr class="hr" />
	<?php
}
add_action( 'pwrcap_do_cf7_captchas_section', 'pwrcap_add_cf7_captcha_setting_section_explanation', 10 );

