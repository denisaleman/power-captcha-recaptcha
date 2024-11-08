<?php
/**
 * Common integration tweaks for WooCommerce.
 *
 * @package PowerCaptchaReCaptcha/Woo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add WooCommerce captcha settings section.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_add_woo_captcha_setting_section() {
	global $wp_settings_sections;
	$page = 'pwrcap_captchas_group';
	$id   = 'pwrcap_woo_captcha_settings_section';
	if ( ! isset( $wp_settings_sections[ $page ][ $id ] ) ) {
		add_settings_section(
			$id,
			esc_html__( 'WooCommerce', 'power-captcha-recaptcha' ),
			function () {
				do_action( 'pwrcap_do_woo_captchas_section' );
			},
			$page,
			array(
				'before_section' => apply_filters( 'pwrcap_woo_captchas_before_section', '' ),
				'after_section'  => apply_filters( 'pwrcap_woo_captchas_after_section', '' ),
				'section_class'  => apply_filters( 'pwrcap_woo_captchas_section_class', null ),
			)
		);
	}
}
add_action( 'pwrcap_admin_init', 'pwrcap_add_woo_captcha_setting_section', 10 );
