<?php
/**
 * Api functions.
 *
 * @package PowerCaptchaReCaptcha/Api
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get plugin option.
 *
 * @since 1.0.0
 *
 * @param string $group Option Group.
 * @param string $key Option key.
 * @return mixed Option value.
 */
function pwrcap_option( $group, $key ) {
	if ( empty( $key ) ) {
		return;
	}

	$plugin_options = wp_parse_args( (array) get_option( "pwrcap_{$group}_options" ), pwrcap_get_options_defaults( $group ) );

	$value = null;

	if ( isset( $plugin_options[ $key ] ) ) {
		$value = $plugin_options[ $key ];
	}

	return $value;
}

/**
 * Update plugin option.
 *
 * @since 1.0.0
 *
 * @param string $group Option Group.
 * @param string $key   Option key.
 * @param mixed  $value Value.
 * @return bool True if the value was updated, false otherwise. @see update_option()
 *
 * @throws \Exception In case $group or $key are empty strings.
 */
function pwrcap_update_option( $group, $key, $value ) {
	if ( '' === $group ) {
		throw new \Exception( 'Option group must be specified.', 1 );
	}

	if ( '' === $key ) {
		throw new \Exception( 'Option key must be specified.', 1 );
	}

	$old_value = get_option( "pwrcap_{$group}_options" );
	$defaults  = wp_parse_args( $old_value ? $old_value : array(), pwrcap_get_options_defaults( $group ) );
	$value     = wp_parse_args( array( $key => $value ), $defaults );

	return update_option( "pwrcap_{$group}_options", $value );
}

/**
 * Get captcha type.
 *
 * @since 1.0.0
 *
 * @return string Type of captcha.
 */
function pwrcap_get_captcha_type() {
	$plugin_options = wp_parse_args( (array) get_option( 'pwrcap_general_options' ), pwrcap_get_options_defaults( 'general' ) );

	if ( 'v3' === $plugin_options['captcha_type'] ) {
		return $plugin_options['captcha_type'];
	} elseif ( 'v2' === $plugin_options['captcha_type'] ) {
		return $plugin_options['captcha_v2_type'];
	}

	/**
	 * Filter the CAPTCHA type determined by the plugin.
	 *
	 * This filter allows developers to modify the CAPTCHA type that will be used
	 * based on the plugin settings. The filtering occurs after the plugin has
	 * determined the CAPTCHA type from the general options (either 'v3' for reCAPTCHA v3,
	 * or 'v2cbx'/'v2inv' for reCAPTCHA v2 variants).
	 *
	 * @since 1.3.0
	 *
	 * @param string $captcha_type The CAPTCHA type determined from plugin options.
	 *                             Possible values: 'v3', 'v2cbx', 'v2inv'.
	 * @param array  $plugin_options The plugin options array from 'pwrcap_general_options'.
	 *                               Contains 'captcha_type' and 'captcha_v2_type' keys.
	 */
	$captcha_type = apply_filters( 'pwrcap_get_captcha_type', $captcha_type, $plugin_options );

	return $captcha_type;
}

/**
 * Get default plugin options.
 *
 * @since 1.0.0
 *
 * @param string $group Options group, ex: 'general', 'captchas' or 'misc'.
 * @return array Default plugin options.
 */
function pwrcap_get_options_defaults( $group ) {
	$defaults = array();

	if ( 'general' === $group ) {
		$defaults['captcha_type']    = 'v3';
		$defaults['captcha_v2_type'] = 'v2cbx';
		$defaults['site_key']        = '';
		$defaults['secret_key']      = '';
	}

	$defaults = apply_filters( "pwrcap_get_{$group}_options_defaults", $defaults );

	return $defaults;
}

/**
 * Check if setup complete.
 *
 * Check if both secret_key and site_key filed out.
 *
 * @since 1.0.0
 *
 * @return array Default plugin options.
 */
function pwrcap_is_setup_complete() {
	$site_key   = pwrcap_option( 'general', 'site_key' );
	$secret_key = pwrcap_option( 'general', 'secret_key' );

	if ( ! empty( $site_key ) && ! empty( $secret_key ) ) {
		return true;
	}

	return false;
}

/**
 * Return captcha API URL.
 *
 * @since 1.0.0
 *
 * @param string $type Type.
 * @return string API URL.
 */
function pwrcap_get_captcha_api_url( $type ) {
	$url = '';

	$site_key = pwrcap_option( 'general', 'site_key' );

	if ( 'v2cbx' === $type ) {
		$url = 'https://www.google.com/recaptcha/api.js?hl=' . esc_attr( get_locale() ) . '&onload=pwrcapInitV2cbx&render=explicit';
	} elseif ( 'v2inv' === $type ) {
		$url = 'https://www.google.com/recaptcha/api.js?hl=' . esc_attr( get_locale() ) . '&onload=pwrcapInitV2inv&render=explicit';
	} elseif ( 'v3' === $type ) {
		$url = 'https://www.google.com/recaptcha/api.js?onload=pwrcapInitV3&render=' . esc_attr( $site_key );
	}

	/**
	 * Filter the CAPTCHA API URL.
	 *
	 * This filter allows developers to modify the URL used to load the reCAPTCHA API
	 * script based on the CAPTCHA type being used. This can be useful for customizing
	 * the API parameters or using a different reCAPTCHA endpoint.
	 *
	 * @since 1.3.0
	 *
	 * @param string $url   The CAPTCHA API URL.
	 * @param string $type  The type of CAPTCHA being used.
	 *                      Possible values: 'v2cbx', 'v2inv', 'v3'.
	 */
	$url = apply_filters( 'pwrcap_get_captcha_api_url', $url, $type );

	return $url;
}
