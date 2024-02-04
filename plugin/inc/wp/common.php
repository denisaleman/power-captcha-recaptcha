<?php
/**
 * Common integration tweaks for WordPress.
 *
 * @package PowerCaptchaReCaptcha/Wp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add WordPress captcha settings section.
 *
 * @return void
 */
function pwrcap_add_wp_captcha_setting_section() {
	global $wp_settings_sections;
	$page = 'pwrcap_captchas_group';
	$id   = 'pwrcap_wp_captcha_settings_section';
	if ( ! isset( $wp_settings_sections[ $page ][ $id ] ) ) {
		add_settings_section(
			$id,
			esc_html__( 'WordPress', 'power-captcha-recaptcha' ),
			function () {
				do_action( 'pwrcap_do_wp_captchas_section' );
			},
			$page,
			array(
				'before_section' => apply_filters( 'pwrcap_wp_captchas_before_section', '' ),
				'after_section'  => apply_filters( 'pwrcap_wp_captchas_after_section', '' ),
				'section_class'  => apply_filters( 'pwrcap_wp_captchas_section_class', null ),
			)
		);
	}
}
add_action( 'pwrcap_admin_init', 'pwrcap_add_wp_captcha_setting_section', 0, 0 );
