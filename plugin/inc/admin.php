<?php
/**
 * Admin functions
 *
 * @package PowerCaptchaReCaptcha/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Render admin page.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_options_page() {
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<div id="poststuff">

			<div id="post-body" class="metabox-holder columns-2">

				<div id="post-body-content">

					<div class="nav-tab-wrapper pwrcap-nav-tab-wrapper">
						<?php do_action( 'pwrcap_admin_do_tab_navigation' ); ?>
					</div><!-- .tab-wrapper -->

					<div class="tabs-stage">
						<?php do_action( 'pwrcap_admin_do_tab_stage' ); ?>
					</div><!-- .tabs-stage -->


				</div><!-- #post-body-content -->

				<div id="postbox-container-1" class="postbox-container pwrcap-postbox-container">

					<div class="meta-box-sortables">
						<div class="postbox">
							<div class="inside">
								<h2><?php esc_html_e( 'Google reCAPTCHA', 'power-captcha-recaptcha' ); ?></h2>
								<ul>
									<li><a href="https://www.google.com/recaptcha/about/" target="_blank"><?php esc_html_e( 'About Google reCAPTCHA', 'power-captcha-recaptcha' ); ?></a></li>
									<li><a href="https://www.google.com/recaptcha/admin" target="_blank"><?php esc_html_e( 'reCAPTCHA Admin Console', 'power-captcha-recaptcha' ); ?></a></li>
								</ul>
							</div> <!-- .inside -->

						</div><!-- .postbox -->
					</div><!-- .meta-box-sortables -->

				</div><!-- #postbox-container-1 .postbox-container -->

			</div><!-- #post-body -->
		</div><!-- #poststuff -->

	</div><!-- .wrap -->
	<?php
}

/**
 * Print a link for the General tab in admin navigation.
 *
 * @since 1.0.11
 *
 * @return void
 */
function pwrcap_print_general_tab_link() {
	?>
	<a href="#tab-general" class="nav-tab nav-tab-active"><?php esc_html_e( 'General', 'power-captcha-recaptcha' ); ?></a>
	<?php
}
add_action( 'pwrcap_admin_do_tab_navigation', 'pwrcap_print_general_tab_link', 10 );

/**
 * Print content for the general tab in admin navigation.
 *
 * @since 1.0.11
 *
 * @return void
 */
function pwrcap_print_general_tab_content() {
	?>
	<div id="tab-general" style="display: block;" class="pwrcap-tab-content meta-box-sortables ui-sortable">
		<div class="postbox">
			<div class="inside">
				<form class="pwrcap_settings_form" action="options.php" method="post">
					<?php settings_fields( 'pwrcap_general_group' ); ?>
					<?php do_settings_sections( 'pwrcap_general_group' ); ?>
					<?php
					submit_button(
						esc_html__( 'Save Changes', 'default' ), // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch 
						'primary pwrcap-sumbit-button',
						'submit',
						false
					);
					?>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
	</div><!-- .meta-box-sortables -->
	<?php
}
add_action( 'pwrcap_admin_do_tab_stage', 'pwrcap_print_general_tab_content', 10 );

/**
 * Print a link for the captchas tab in admin navigation.
 *
 * @since 1.0.11
 *
 * @return void
 */
function pwrcap_print_captchas_tab_link() {
	?>
	<a href="#tab-captchas" class="nav-tab"><?php esc_html_e( 'Captchas', 'power-captcha-recaptcha' ); ?></a>
	<?php
}
add_action( 'pwrcap_admin_do_tab_navigation', 'pwrcap_print_captchas_tab_link', 20 );

/**
 * Print content for the captchas tab in admin navigation.
 *
 * @since 1.0.11
 *
 * @return void
 */
function pwrcap_print_captchas_tab_content() {
	?>
	<div id="tab-captchas" class="pwrcap-tab-content meta-box-sortables ui-sortable">
		<div class="postbox">
			<div class="inside">
				<form class="pwrcap_settings_form" action="options.php" method="post">
					<?php settings_fields( 'pwrcap_captchas_group' ); ?>
					<?php do_settings_sections( 'pwrcap_captchas_group' ); ?>
					<?php
					submit_button(
						esc_html__( 'Save Changes', 'default' ), // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch 
						'primary pwrcap-sumbit-button',
						'submit',
						false
					);
					?>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
	</div><!-- .meta-box-sortables -->
	<?php
}
add_action( 'pwrcap_admin_do_tab_stage', 'pwrcap_print_captchas_tab_content', 20 );

/**
 * Print a link for the captchas tab in admin navigation.
 *
 * @since 1.0.11
 *
 * @return void
 */
function pwrcap_print_misc_tab_link() {
	?>
	<a href="#tab-misc" class="nav-tab"><?php esc_html_e( 'Misc', 'power-captcha-recaptcha' ); ?></a>
	<?php
}
add_action( 'pwrcap_admin_do_tab_navigation', 'pwrcap_print_misc_tab_link', 30 );

/**
 * Print content for the captchas tab in admin navigation.
 *
 * @since 1.0.11
 *
 * @return void
 */
function pwrcap_print_misc_tab_content() {
	?>
	<div id="tab-misc" class="pwrcap-tab-content meta-box-sortables ui-sortable">
		<div class="postbox">
			<div class="inside">
				<form class="pwrcap_settings_form" action="options.php" method="post">
					<?php settings_fields( 'pwrcap_misc_group' ); ?>
					<?php do_settings_sections( 'pwrcap_misc_group' ); ?>
					<?php
					submit_button(
						esc_html__( 'Save Changes', 'default' ), // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch 
						'primary pwrcap-sumbit-button',
						'submit',
						false
					);
					?>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
	</div><!-- .meta-box-sortables -->
	<?php
}
add_action( 'pwrcap_admin_do_tab_stage', 'pwrcap_print_misc_tab_content', 30 );

/**
 * Register menu page.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_register_menu() {
	add_submenu_page( 'options-general.php', esc_html__( 'Power Captcha reCAPTCHA', 'power-captcha-recaptcha' ), esc_html__( 'Power Captcha', 'power-captcha-recaptcha' ), 'manage_options', 'pwrcap-settings', 'pwrcap_options_page' );
}
add_action( 'admin_menu', 'pwrcap_register_menu' );

/**
 * Display key section content.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_content_key_section() {
	do_action( 'pwrcap_do_key_section' );
}

/**
 * Display misc section content.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_content_misc_section() {
	do_action( 'pwrcap_do_misc_section' );
}

/**
 * Register site_key field.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_field_site_key() {
	$site_key = pwrcap_option( 'general', 'site_key' );
	?>
	<input type="text" name="pwrcap_general_options[site_key]" id="site_key" class="regular-text" value="<?php echo esc_attr( $site_key ); ?>" />
	<?php
}

/**
 * Register secret_key field.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_field_secret_key() {
	$secret_key = pwrcap_option( 'general', 'secret_key' );
	?>
	<input type="text" name="pwrcap_general_options[secret_key]" id="secret_key" class="regular-text" value="<?php echo esc_attr( $secret_key ); ?>" />
	<?php
}

/**
 * Register enable_debug field.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_field_enable_debug() {
	$enable_debug = pwrcap_option( 'misc', 'enable_debug' );
	?>
	<label><input type="checkbox" name="pwrcap_misc_options[enable_debug]" id="enable_debug" value="1" <?php checked( 1, $enable_debug ); ?> /></label>
	<p class="description"><?php esc_html_e( 'Output captcha verification response to the browser console.', 'power-captcha-recaptcha' ); ?></p>
	<?php
}

/**
 * Register plugin option fields.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_register_plugin_option_fields() {
	register_setting( 'pwrcap_general_group', 'pwrcap_general_options', 'pwrcap_sanitize_general_options' );
	register_setting( 'pwrcap_captchas_group', 'pwrcap_captchas_options', 'pwrcap_sanitize_captchas_options' );
	register_setting( 'pwrcap_misc_group', 'pwrcap_misc_options', 'pwrcap_sanitize_misc_options' );
	register_setting( 'pwrcap_state_group', 'pwrcap_state_options', 'pwrcap_sanitize_state_options' );

	add_settings_section(
		'pwrcap_key_section',
		esc_html__( 'Setup', 'power-captcha-recaptcha' ),
		'pwrcap_content_key_section',
		'pwrcap_general_group',
		array(
			'before_section' => apply_filters( 'pwrcap_key_section_before', '' ),
			'after_section'  => apply_filters( 'pwrcap_key_section_after', '' ),
			'section_class'  => apply_filters( 'pwrcap_key_section_class', null ),
		)
	);
	add_settings_field( 'captcha_type', esc_html__( 'reCAPTCHA Type', 'power-captcha-recaptcha' ), 'pwrcap_field_captcha_type', 'pwrcap_general_group', 'pwrcap_key_section' );
	add_settings_field( 'site_key', esc_html__( 'Site Key', 'power-captcha-recaptcha' ), 'pwrcap_field_site_key', 'pwrcap_general_group', 'pwrcap_key_section' );
	add_settings_field( 'secret_key', esc_html__( 'Secret Key', 'power-captcha-recaptcha' ), 'pwrcap_field_secret_key', 'pwrcap_general_group', 'pwrcap_key_section' );

	add_settings_section(
		'pwrcap_misc_section',
		esc_html__( 'Misc', 'power-captcha-recaptcha' ),
		'pwrcap_content_misc_section',
		'pwrcap_misc_group',
		array(
			'before_section' => apply_filters( 'pwrcap_misc_section_before', '' ),
			'after_section'  => apply_filters( 'pwrcap_misc_section_after', '' ),
			'section_class'  => apply_filters( 'pwrcap_misc_section_class', null ),
		)
	);
	add_settings_field(
		'enable_debug',
		esc_html__( 'Debug Mode', 'power-captcha-recaptcha' ),
		'pwrcap_field_enable_debug',
		'pwrcap_misc_group',
		'pwrcap_misc_section',
		array(
			'class' => apply_filters( 'pwrcap_debug_mode_enable_field_class', '' ),
		)
	);

	do_action( 'pwrcap_admin_init' );
}
add_action( 'admin_init', 'pwrcap_register_plugin_option_fields' );

/**
 * Sanitize general plugin options.
 *
 * @since 1.0.0
 *
 * @param array $input Options.
 * @return array Sanitized options.
 */
function pwrcap_sanitize_general_options( $input ) {
	if ( isset( $input['captcha_type'] ) ) {
		$input['captcha_type'] = sanitize_text_field( $input['captcha_type'] );
	}

	if ( 'v2' === $input['captcha_type'] ) {
		if ( isset( $input['captcha_v2_type'] ) ) {
			$input['captcha_v2_type'] = sanitize_text_field( $input['captcha_v2_type'] );
		}
	}

	if ( isset( $input['site_key'] ) ) {
		$input['site_key'] = sanitize_text_field( $input['site_key'] );
	}

	if ( isset( $input['secret_key'] ) ) {
		$input['secret_key'] = sanitize_text_field( $input['secret_key'] );
	}

	$input = apply_filters( 'pwrcap_sanitize_general_options', $input );

	return $input;
}

/**
 * Sanitize captchas plugin options.
 *
 * @since 1.0.0
 *
 * @param array $input Options.
 * @return array Sanitized options.
 */
function pwrcap_sanitize_captchas_options( $input ) {
	$input = apply_filters( 'pwrcap_sanitize_captchas_options', $input );

	return $input;
}

/**
 * Sanitize misc plugin options.
 *
 * @since 1.0.0
 *
 * @param array $input Options.
 * @return array Sanitized options.
 */
function pwrcap_sanitize_misc_options( $input ) {
	if ( isset( $input['enable_debug'] ) ) {
		$input['enable_debug'] = ( isset( $input['enable_debug'] ) && (bool) $input['enable_debug'] ) ? 1 : 0;
	}

	$input = apply_filters( 'pwrcap_sanitize_misc_options', $input );

	return $input;
}

/**
 * Sanitize state plugin options.
 *
 * @since 1.0.0
 *
 * @param array $input Options.
 * @return array Sanitized options.
 */
function pwrcap_sanitize_state_options( $input ) {
	$input = apply_filters( 'pwrcap_sanitize_state_options', $input );

	return $input;
}

/**
 * Display admin notices.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_display_admin_message() {
	do_action( 'pwrcap_do_admin_notices' );
}
add_action( 'admin_notices', 'pwrcap_display_admin_message' );

/**
 * Load admin scripts and styles.
 *
 * @since 1.0.0
 *
 * @param string $hook Hook name.
 */
function pwrcap_load_admin_scripts( $hook ) {
	if ( 'settings_page_pwrcap-settings' !== $hook ) {
		return;
	}

	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	wp_enqueue_style( 'pwrcap-admin-style', PWRCAP_URL . '/assets/css/admin' . $min . '.css', array(), PWRCAP_VERSION );
	wp_enqueue_script( 'pwrcap-admin-script', PWRCAP_URL . '/assets/js/admin' . $min . '.js', array( 'jquery' ), PWRCAP_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'pwrcap_load_admin_scripts' );

/**
 * Render captcha_type field.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_field_captcha_type() {
	$captcha_type       = pwrcap_option( 'general', 'captcha_type' );
	$captcha_v2_type    = pwrcap_option( 'general', 'captcha_v2_type' );
	$captcha_v2_checked = ( 'v2' === $captcha_type ) ? true : false;
	?>
	<fieldset>
		<label><input class="captcha-type-radio" type="radio" name="pwrcap_general_options[captcha_type]" value="v3" <?php checked( 'v3', $captcha_type ); ?> /><?php esc_html_e( 'Score based (v3)', 'power-captcha-recaptcha' ); ?> <p class="description"><?php esc_html_e( 'Verify requests with a score', 'power-captcha-recaptcha' ); ?></p></label>
		<br>
		<label><input class="captcha-type-radio" type="radio" name="pwrcap_general_options[captcha_type]" value="v2" <?php checked( 'v2', $captcha_type ); ?> /><?php esc_html_e( 'Challenge (v2)', 'power-captcha-recaptcha' ); ?> <p class="description"><?php esc_html_e( 'Verify requests with a challenge', 'power-captcha-recaptcha' ); ?></p></label>
		<br>
		<fieldset id="fieldset-captcha-v2-type" class="pwrcap-fieldset pwrcap-fieldset--sub" <?php echo ! $captcha_v2_checked ? 'style="display:none;"' : ''; ?>>
			<label><input class="captcha-v2-type-radio" type="radio" name="pwrcap_general_options[captcha_v2_type]" value="v2cbx" <?php checked( 'v2cbx', $captcha_v2_type ); ?> /><?php esc_html_e( '"I\'m not a robot" Checkbox', 'power-captcha-recaptcha' ); ?> <p class="description"><?php esc_html_e( 'Validate requests with the "I\'m not a robot" checkbox', 'power-captcha-recaptcha' ); ?></p> </label>
			<br>
			<label><input class="captcha-v2-type-radio" type="radio" name="pwrcap_general_options[captcha_v2_type]" value="v2inv" <?php checked( 'v2inv', $captcha_v2_type ); ?> /><?php esc_html_e( 'Invisible reCAPTCHA badge', 'power-captcha-recaptcha' ); ?> <p class="description"><?php esc_html_e( 'Validate requests in the background', 'power-captcha-recaptcha' ); ?></p> </label>
		</fieldset>
	</fieldset>
	<?php
}


/**
 * Provide enable_login setting field default value.
 *
 * @since 1.0.0
 *
 * @param array $defaults Array of default options values.
 * @return array Modified array of default options values.
 */
function pwrcap_notice_greeting_dismissed_default( $defaults ) {
	$defaults['notice_greeting_dismissed'] = 0;
	return $defaults;
}
add_filter( 'pwrcap_get_state_options_defaults', 'pwrcap_notice_greeting_dismissed_default', 10, 1 );

/**
 * Provide enable_login setting field default value.
 *
 * @since 1.0.0
 *
 * @param array $defaults Array of default options values.
 * @return array Modified array of default options values.
 */
function pwrcap_notice_not_configured_dismissed_default( $defaults ) {
	$defaults['notice_not_configured_dismissed'] = 0;
	return $defaults;
}
add_filter( 'pwrcap_get_state_options_defaults', 'pwrcap_notice_not_configured_dismissed_default', 10, 1 );

/**
 * Consider sanitize `notice_not_configured_dissmissed` and `notice_greeting_dissmissed`
 *
 * @todo // phpcs:ignore Generic.Commenting.Todo.CommentFound
 */

/**
 * Show notice with greeting.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_notice_greeting() {
	$has_dissmissed = pwrcap_option( 'state', 'notice_greeting_dismissed' );
	if ( $has_dissmissed ) {
		return;
	}

	$link = admin_url( 'options-general.php?page=pwrcap-settings#tab-general' );

	if ( ! pwrcap_is_setup_complete() ) {
		$message = __( 'Thank you for installing Power Captcha reCAPTCHA! Complete setup to start protecting your site now.', 'power-captcha-recaptcha' );
		$button  = __( 'Complete Setup', 'power-captcha-recaptcha' );
	} else {
		$message = __( 'Thank you for installing Power Captcha reCAPTCHA!', 'power-captcha-recaptcha' );
		$button  = false;
	}

	?>
	<div class="notice notice-success pwrcap-notice-greeting is-dismissible">
		<?php echo wp_kses_post( wpautop( $message ) ); ?>
		<?php if ( $button ) : ?>
		<a class="button" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $button ); ?></a>
		<p></p>
		<?php endif; ?>
	</div>
	<?php
	add_action( 'pwrcap_do_admin_notices',
		function() {
			pwrcap_update_option( 'state', 'notice_greeting_dismissed', true );
		},
		PHP_INT_MAX
	);
}
add_action( 'pwrcap_do_admin_notices', 'pwrcap_notice_greeting' );

/**
 * Show notice that captcha is not configured.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_notice_not_configured() {
	if ( pwrcap_is_setup_complete() ) {
		return;
	}

	$has_dissmissed = pwrcap_option( 'state', 'notice_not_configured_dismissed' );
	if ( $has_dissmissed ) {
		return;
	}

	$has_dissmissed_greeting = pwrcap_option( 'state', 'notice_greeting_dismissed' );
	if ( ! $has_dissmissed_greeting ) {
		return;
	}

	$action  = 'dissmiss_notice_not_configured';
	$nonce   = wp_create_nonce( $action );
	$message = __( 'Power Captcha: reCAPTCHA is still not configured! Complete setup to start protecting your site now.', 'power-captcha-recaptcha' );
	$link    = admin_url( 'options-general.php?page=pwrcap-settings' );
	$button  = __( 'Complete Setup', 'power-captcha-recaptcha' );

	?>
	<div class="notice notice-error pwrcap-notice-not-configured is-dismissible" data-nonce="<?php echo esc_html( $nonce ); ?>" data-action="<?php echo esc_html( $action ); ?>">
		<?php echo wp_kses_post( wpautop( $message ) ); ?>
		<a class="button" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $button ); ?></a>
		<p></p>
	</div>
	<?php
}
add_action( 'pwrcap_do_admin_notices', 'pwrcap_notice_not_configured' );

/**
 * Undissmiss notice that captcha is not configured.
 *
 * @return void
 */
function pwrcap_undismiss_notice_not_configured() {
	pwrcap_update_option( 'state', 'notice_greeting_dismissed', false );
}
add_action( 'pwrcap_daily_event', 'pwrcap_undismiss_notice_not_configured', 10, 0 );


/**
 * Dissmiss notice that captcha is not configured.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_dismiss_notice_not_configured() {
	$nonce  = sanitize_text_field( wp_unslash( isset( $_POST['nonce'] ) ? $_POST['nonce'] : '' ) );
	$action = sanitize_text_field( wp_unslash( isset( $_POST['action'] ) ? $_POST['action'] : '' ) );
	if ( 'dismiss_notice_not_configured' !== $action || ! wp_verify_nonce( $nonce, $action ) ) {
		return;
	}
	pwrcap_update_option( 'state', 'notice_not_configured_dismissed', true );
}
add_action( 'wp_ajax_pwrcap_dismiss_notice_not_configured', 'pwrcap_dismiss_notice_not_configured' );

/**
 * Enqueue admin script handles notices.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_load_notice_script() {
	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	wp_enqueue_script( 'pwrcap-notice-script', PWRCAP_URL . '/assets/js/notice' . $min . '.js', array( 'jquery' ), PWRCAP_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'pwrcap_load_notice_script' );

/**
 * Provide message if setup is not complete.
 *
 * @since 1.0.0
 *
 * @return void
 */
function pwrcap_complete_setup_message() {
	if ( pwrcap_is_setup_complete() ) {
		return;
	}
	?>
	<p>
	<?php
	if ( current_action() === 'pwrcap_do_key_section' ) {
		/* translators: 1: link open, 2: link close, 3: line break */
		printf( esc_html__( 'To start using reCAPTCHA, complete setup: %1$ssign up in Google reCAPTCHA%2$s for keys. Fill in site key and secret key bellow.', 'power-captcha-recaptcha' ), '<a href="https://www.google.com/recaptcha/admin" target="_blank">', '</a>', '<br />' );
	} else {
		/* translators: 1: link open, 2: link close */
		printf( esc_html__( 'Complete setup to start using reCAPTCHA. %1$sComplete setup »%2$s ', 'power-captcha-recaptcha' ), '<a href="' . esc_url( admin_url( '/options-general.php?page=pwrcap-settings#tab-general' ) ) . '">', '</a>', '<br />' );
	}
	?>
	</p>
	<?php
}
add_action( 'pwrcap_do_key_section', 'pwrcap_complete_setup_message' );
add_action( 'pwrcap_do_misc_section', 'pwrcap_complete_setup_message' );
add_action( 'pwrcap_do_woo_captchas_section', 'pwrcap_complete_setup_message' );
add_action( 'pwrcap_do_wp_captchas_section', 'pwrcap_complete_setup_message' );

/**
 * Provide html class to visually deactivate field.
 *
 * @since 1.0.0
 *
 * @param string $class Field class.
 * @return string Modified field class.
 */
function pwrcap_deactivate_fields_if_not_configured( $class ) {
	if ( pwrcap_is_setup_complete() ) {
		return $class;
	}
	return 'pwrcap-field-inactive';
}
add_filter( 'pwrcap_woo_checkout_form_enable_field_class', 'pwrcap_deactivate_fields_if_not_configured', 10, 1 );
add_filter( 'pwrcap_woo_login_form_enable_field_class', 'pwrcap_deactivate_fields_if_not_configured', 10, 1 );
add_filter( 'pwrcap_woo_lostpassword_form_enable_field_class', 'pwrcap_deactivate_fields_if_not_configured', 10, 1 );
add_filter( 'pwrcap_woo_register_form_enable_field_class', 'pwrcap_deactivate_fields_if_not_configured', 10, 1 );
add_filter( 'pwrcap_woo_resetpassword_form_enable_field_class', 'pwrcap_deactivate_fields_if_not_configured', 10, 1 );
add_filter( 'pwrcap_woo_review_form_enable_field_class', 'pwrcap_deactivate_fields_if_not_configured', 10, 1 );
add_filter( 'pwrcap_comment_form_enable_field_class', 'pwrcap_deactivate_fields_if_not_configured', 10, 1 );
add_filter( 'pwrcap_login_form_enable_field_class', 'pwrcap_deactivate_fields_if_not_configured', 10, 1 );
add_filter( 'pwrcap_lostpassword_form_enable_field_class', 'pwrcap_deactivate_fields_if_not_configured', 10, 1 );
add_filter( 'pwrcap_register_form_enable_field_class', 'pwrcap_deactivate_fields_if_not_configured', 10, 1 );
add_filter( 'pwrcap_resetpassword_form_enable_field_class', 'pwrcap_deactivate_fields_if_not_configured', 10, 1 );
add_filter( 'pwrcap_debug_mode_enable_field_class', 'pwrcap_deactivate_fields_if_not_configured', 10, 1 );
