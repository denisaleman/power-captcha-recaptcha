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

	return $url;
}
