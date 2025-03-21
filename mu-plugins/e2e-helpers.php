<?php 

function add_test_environment_meta_tag() {
	echo '<meta name="environment" content="test">' . PHP_EOL;
}
add_action('wp_head', 'add_test_environment_meta_tag');

function e2e_provide_google_test_key_hostname( $hostname ) {
    if ( ! isset( $_COOKIE['pwrcap-e2e-test'] ) || $_COOKIE['pwrcap-e2e-test'] !== '1' ) {
        return $hostname;
    }

    return "testkey.google.com";
}
add_filter( 'pwrcap_recaptcha_expected_hostname', 'e2e_provide_google_test_key_hostname', 10, 1 );

function e2e_delete_plugin_data() {
    if (!isset($_GET['delete_plugin_data'])) {
        return;
    }

    global $wpdb;

    $table_name = $wpdb->prefix . 'pwrcap_captcha_activity_records';
    $wpdb->query("DROP TABLE IF EXISTS `$table_name`");

	$options = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s",
			$wpdb->esc_like('pwrcap_') . '%'
		)
	);

	foreach ($options as $option_name) {
		delete_option($option_name);
	}

    echo "OK";
	die;
}
add_action('init', 'e2e_delete_plugin_data');

function e2e_create_test_admin() {
	if ( ! isset($_GET['create_test_admin']) ) {
		return;
	}

	$login    = 'e2e-test';
	$password = 'acF2!3$532%yfaw';
	$email    = 'e2e-test@example.com';

	if ( username_exists( $login ) ) {
		echo "OK";
		die;
	}

	$user_id = wp_create_user( $login, $password, $email );

	if ( is_wp_error( $user_id ) ) {
		echo "Error: " . $user_id->get_error_message();
		die;
	}

	// Set role to administrator
	$user = new WP_User( $user_id );
	$user->set_role( 'administrator' );

	echo "OK";
	die;
}
add_action( 'init', 'e2e_create_test_admin' );

function e2e_deactivate_plugin() {
    if (!isset($_GET['deactivate_plugin'])) {
        return;
    }

	if( ! empty($_GET['deactivate_plugin']) && $_GET['deactivate_plugin'] != '1') {
		$slug = sanitize_text_field( $_GET['deactivate_plugin'] );

		if ( is_plugin_active( $slug ) ) {
			deactivate_plugins( $slug );
			echo "OK";
			die;
		}
	}

	// backwards compat
    $plugin_file = 'power-captcha-recaptcha/power-captcha-recaptcha.php';

    if (is_plugin_active($plugin_file)) {
        deactivate_plugins($plugin_file);
    }
	
	echo "OK";
    die;
}
add_action('init', 'e2e_deactivate_plugin');

/**
 * Endpoint to deactivate a plugin by slug for E2E testing.
 *
 * Example usage:
 * http://localhost/?e2e_deactivate_plugin=contact-form-7/wp-contact-form-7.php
 */
function e2e_deactivate_plugin_by_slug() {
	if ( ! isset( $_GET['e2e_deactivate_plugin'] ) ) {
		return;
	}

	if ( ! current_user_can( 'activate_plugins' ) ) {
		echo 'Permission denied';
		exit;
	}

	$slug = sanitize_text_field( $_GET['e2e_deactivate_plugin'] );

	if ( is_plugin_active( $slug ) ) {
		deactivate_plugins( $slug );
		echo 'Plugin deactivated';
		exit;
	}

	echo 'Plugin already inactive';
	exit;
}
add_action( 'init', 'e2e_deactivate_plugin_by_slug' );

/**
 * http://localhost/?enable_registration=1
 *
 * @return void
 */
function e2e_enable_user_registration_option() {
	if ( ! isset( $_GET['enable_registration'] ) ) {
		return;
	}

	// Sanitize the input (optional toggle behavior)
	$enable = sanitize_text_field( $_GET['enable_registration'] );

	// Enable or disable based on the passed value
	if ( $enable === '1' ) {
		update_option( 'users_can_register', 1 );
		echo 'OK';
	} elseif ( $enable === '0' ) {
		update_option( 'users_can_register', 0 );
		echo 'OK';
	} else {
		echo 'INVALID VALUE. USE ?enable_registration=1 OR =0';
	}

	exit;
}
add_action( 'init', 'e2e_enable_user_registration_option' );

/**
 * Enable WooCommerce account creation via endpoint
 * Example: http://localhost/?enable_woocommerce_registration=1
 */
function e2e_enable_woocommerce_registration() {
	if ( ! isset( $_GET['enable_woocommerce_registration'] ) ) {
		return;
	}

	if ( ! function_exists( 'WC' ) ) {
		echo 'WooCommerce not active';
		exit;
	}

	// Enable "Allow customers to create an account on the My account page"
	update_option( 'woocommerce_enable_myaccount_registration', 'yes' );

	// Automatically generate username from email
	update_option( 'woocommerce_registration_generate_password', 'no' );

	// // Optional: Automatically generate password
	// update_option( 'woocommerce_registration_generate_password', 'yes' );

	echo 'OK';
	exit;
}
add_action( 'init', 'e2e_enable_woocommerce_registration' );

/**
 * Endpoint to check if a user with given identifier (username or email)
 * has a specific role.
 *
 * Example:
 *   http://localhost/?check_user=e2e-test&expected_role=administrator
 *   http://localhost/?check_user=e2e@example.com&expected_role=subscriber
 *
 * @return void
 */
function e2e_check_user_role() {
	if ( ! isset( $_GET['check_user'], $_GET['expected_role'] ) ) {
		return;
	}

	$input         = sanitize_text_field( $_GET['check_user'] );
	$expected_role = sanitize_key( $_GET['expected_role'] );

	if ( is_email( $input ) ) {
		$user = get_user_by( 'email', sanitize_email( $input ) );
	} else {
		$user = get_user_by( 'login', $input );
	}

	if ( $user && in_array( $expected_role, (array) $user->roles, true ) ) {
		echo 'FOUND';
	} else {
		echo 'NOT FOUND';
	}

	exit;
}
add_action( 'init', 'e2e_check_user_role' );

/**
 * http://localhost/?create_user=e2e-test&role=subscriber&email=test@example.com&password=secret123
 *
 * @return void
 */
function e2e_create_user_by_username() {
	if ( ! isset( $_GET['create_user'] ) ) {
		return;
	}

	$username = sanitize_user( $_GET['create_user'] );
	$email    = isset( $_GET['email'] ) ? sanitize_email( $_GET['email'] ) : $username . '@example.com';
	$password = isset( $_GET['password'] ) ? sanitize_text_field( $_GET['password'] ) : wp_generate_password();
	$role     = isset( $_GET['role'] ) ? sanitize_key( $_GET['role'] ) : 'subscriber';

	require_once ABSPATH . 'wp-admin/includes/user.php';

	// Delete existing user by username
	if ( $user = get_user_by( 'login', $username ) ) {
		wp_delete_user( $user->ID, 1 );
	}

	// Delete existing user by email (if different from username)
	if ( $user = get_user_by( 'email', $email ) ) {
		wp_delete_user( $user->ID, 1 );
	}

	// Now create the user
	$user_id = wp_create_user( $username, $password, $email );

	if ( is_wp_error( $user_id ) ) {
		echo 'FAILED TO CREATE USER';
		exit;
	}

	wp_update_user( [
		'ID'   => $user_id,
		'role' => $role,
	] );

	echo 'OK';
	exit;
}
add_action( 'init', 'e2e_create_user_by_username' );


/**
 * Confirm user by username (bypass email confirmation).
 * Example: http://localhost/?confirm_user=e2e-test-user
 *
 * @return void
 */
function e2e_confirm_user_by_username() {
	if ( ! isset( $_GET['confirm_user'] ) ) {
		return;
	}

	$username = sanitize_text_field( $_GET['confirm_user'] );
	$user     = get_user_by( 'login', $username );

	if ( ! $user ) {
		echo 'USER NOT FOUND';
		exit;
	}

	global $wpdb;

	// Clear activation key (used by some plugins to check confirmation)
	$wpdb->update(
		$wpdb->users,
		[ 'user_activation_key' => '' ],
		[ 'ID' => $user->ID ]
	);

	// If a plugin uses meta to track confirmation, clear custom meta too
	delete_user_meta( $user->ID, 'account_activation_key' );
	delete_user_meta( $user->ID, 'needs_confirmation' );
	delete_user_meta( $user->ID, 'unconfirmed' );

	do_action( 'e2e_user_confirmed', $user );

	echo 'OK';
	exit;
}
add_action( 'init', 'e2e_confirm_user_by_username' );

/**
 * Endpoint to delete a user by username or email.
 * Usage:
 *   http://localhost/?delete_user=e2e-test
 *   http://localhost/?delete_user=e2e@example.com
 *
 * @return void
 */
function e2e_delete_user_by_identifier() {
	if ( ! isset( $_GET['delete_user'] ) ) {
		return;
	}

	$input = sanitize_text_field( $_GET['delete_user'] );

	if ( is_email( $input ) ) {
		$user = get_user_by( 'email', sanitize_email( $input ) );
	} else {
		$user = get_user_by( 'login', $input );
	}

	if ( ! $user ) {
		echo 'OK';
		exit;
	}

	require_once ABSPATH . 'wp-admin/includes/user.php';

	$deleted = wp_delete_user( $user->ID, 1 ); // reassign content to admin (ID 1)

	echo $deleted ? 'OK' : 'FAILED TO DELETE USER';
	exit;
}
add_action( 'init', 'e2e_delete_user_by_identifier' );

/**
 * http://localhost/?prepare_comment_post=e2e-comment-post-test
 *
 * @return void
 */
function e2e_prepare_comment_post_by_slug() {
	if ( ! isset( $_GET['prepare_comment_post'] ) ) {
		return;
	}

	$slug = sanitize_title( $_GET['prepare_comment_post'] );

	// Check if post with that slug exists
	$existing_post = get_page_by_path( $slug, OBJECT, 'post' );

	if ( $existing_post ) {
		$post_id = $existing_post->ID;

		// Delete comments associated with the post
		$comments = get_comments( [ 'post_id' => $post_id ] );
		foreach ( $comments as $comment ) {
			wp_delete_comment( $comment->comment_ID, true );
		}

		// Delete the post
		wp_delete_post( $post_id, true );
	}

	// Create new post with that slug
	$post_id = wp_insert_post( [
		'post_title'   => ucwords( str_replace( '-', ' ', $slug ) ),
		'post_name'    => $slug,
		'post_status'  => 'publish',
		'post_content' => 'Test content for ' . $slug,
		'post_type'    => 'post',
	] );

	if ( is_wp_error( $post_id ) ) {
		echo 'FAILED TO CREATE POST';
	} else {
		echo 'OK';
	}

	exit;
}
add_action( 'init', 'e2e_prepare_comment_post_by_slug' );

/**
 * Endpoint to delete a post and its comments by slug for E2E teardown.
 *
 * Example usage:
 * http://localhost/?teardown_comment_post=example-post-slug
 */
function e2e_teardown_comment_post_by_slug() {
	if ( ! isset( $_GET['teardown_comment_post'] ) ) {
		return;
	}

	$slug = sanitize_title( $_GET['teardown_comment_post'] );

	// Try to get the post by slug
	$existing_post = get_page_by_path( $slug, OBJECT, 'post' );

	if ( $existing_post ) {
		$post_id = $existing_post->ID;

		// Delete all comments for the post
		$comments = get_comments( [ 'post_id' => $post_id ] );
		foreach ( $comments as $comment ) {
			wp_delete_comment( $comment->comment_ID, true );
		}

		// Delete the post
		wp_delete_post( $post_id, true );

		echo 'OK';
		exit;
	}

	echo 'OK';
	exit;
}
add_action( 'init', 'e2e_teardown_comment_post_by_slug' );

/**
 * Endpoint to create or reset a page by slug for E2E test setup.
 * Example: http://localhost/?prepare_page=test-page
 * Optional: http://localhost/?prepare_page=test-page&content=Custom+page+content
 */
function e2e_prepare_page_by_slug() {
	if ( ! isset( $_GET['prepare_page'] ) ) {
		return;
	}

	$slug = sanitize_title( $_GET['prepare_page'] );

	// Use provided content if available, otherwise fallback
	$content = isset( $_GET['content'] ) ? wp_kses_post( wp_unslash( $_GET['content'] ) ) : 'Test content for ' . $slug;

	// Delete existing page if it exists
	$existing_page = get_page_by_path( $slug, OBJECT, 'page' );
	if ( $existing_page ) {
		wp_delete_post( $existing_page->ID, true );
	}

	// Create a new page
	wp_insert_post( [
		'post_title'   => ucwords( str_replace( '-', ' ', $slug ) ),
		'post_name'    => $slug,
		'post_status'  => 'publish',
		'post_type'    => 'page',
		'post_content' => $content,
	] );

	echo 'OK';
	exit;
}
add_action( 'init', 'e2e_prepare_page_by_slug' );


/**
 * Endpoint to delete a page by slug for E2E teardown.
 * Example: http://localhost/?teardown_page=test-page
 */
function e2e_teardown_page_by_slug() {
	if ( ! isset( $_GET['teardown_page'] ) ) {
		return;
	}

	$slug = sanitize_title( $_GET['teardown_page'] );

	// Get the page by slug
	$existing_page = get_page_by_path( $slug, OBJECT, 'page' );

	if ( $existing_page ) {
		wp_delete_post( $existing_page->ID, true );
	}

	echo 'OK';
	exit;
}
add_action( 'init', 'e2e_teardown_page_by_slug' );

/**
 * http://localhost/?prepare_review_product=e2e-product-slug
 *
 * @return void
 */
function e2e_prepare_review_product_by_slug() {
	if ( ! isset( $_GET['prepare_review_product'] ) ) {
		return;
	}

	$slug = sanitize_title( $_GET['prepare_review_product'] );

	// Check if product with that slug exists
	$existing_product = get_page_by_path( $slug, OBJECT, 'product' );

	if ( $existing_product ) {
		$product_id = $existing_product->ID;

		// Delete comments (product reviews) associated with the product
		$comments = get_comments( [ 'post_id' => $product_id ] );
		foreach ( $comments as $comment ) {
			wp_delete_comment( $comment->comment_ID, true );
		}

		// Delete the product
		wp_delete_post( $product_id, true );
	}

	// Create a new simple product with that slug
	$product_id = wp_insert_post( [
		'post_title'   => ucwords( str_replace( '-', ' ', $slug ) ),
		'post_name'    => $slug,
		'post_status'  => 'publish',
		'post_type'    => 'product',
	] );

	if ( is_wp_error( $product_id ) ) {
		echo 'FAILED TO CREATE PRODUCT';
		exit;
	}

	// Set it as a simple product with basic meta
	update_post_meta( $product_id, '_price', '19.99' );
	update_post_meta( $product_id, '_regular_price', '19.99' );
	update_post_meta( $product_id, '_stock_status', 'instock' );
	update_post_meta( $product_id, '_manage_stock', 'no' );
	update_post_meta( $product_id, '_visibility', 'visible' );
	update_post_meta( $product_id, '_product_version', WC()->version );

	echo 'OK';
	exit;
}
add_action( 'init', 'e2e_prepare_review_product_by_slug' );

/**
 * Endpoint to teardown a review product by slug for E2E testing.
 * Example: http://localhost/?teardown_review_product=my-test-product
 */
function e2e_teardown_review_product_by_slug() {
	if ( ! isset( $_GET['teardown_review_product'] ) ) {
		return;
	}

	$slug = sanitize_title( $_GET['teardown_review_product'] );

	// Find product by slug
	$product = get_page_by_path( $slug, OBJECT, 'product' );

	if ( $product ) {
		$post_id = $product->ID;

		// Delete product reviews (comments)
		$comments = get_comments( [ 'post_id' => $post_id ] );
		foreach ( $comments as $comment ) {
			wp_delete_comment( $comment->comment_ID, true );
		}

		// Delete the product
		wp_delete_post( $post_id, true );

		echo 'OK';
		exit;
	}

	echo 'OK';
	exit;
}
add_action( 'init', 'e2e_teardown_review_product_by_slug' );

/**
 * http://localhost/?get_wc_lost_password_url=1
 *
 * Outputs the WooCommerce lost password URL
 */
function e2e_get_wc_lost_password_url() {
	if ( ! isset( $_GET['get_wc_lost_password_url'] ) ) {
		return;
	}

	$url = wc_lostpassword_url();
	echo esc_url_raw( $url );
	exit;
}
add_action( 'init', 'e2e_get_wc_lost_password_url' );

/**
 * http://localhost/?remove_wc_remove_all_data
 *
 * @return void
 */
function e2e_remove_wc_remove_data_constant() {
	if ( ! isset( $_GET['remove_wc_remove_all_data'] ) ) {
		return;
	}

	$config_path = ABSPATH . 'wp-config.php';

	if ( ! is_writable( $config_path ) ) {
		echo 'ERROR: wp-config.php not writable';
		exit;
	}

	$config_contents = file_get_contents( $config_path );

	$pattern = "/^\s*define\s*\(\s*'WC_REMOVE_ALL_DATA'\s*,\s*true\s*\);\s*\n?/m";

	if ( preg_match( $pattern, $config_contents ) ) {
		$config_contents = preg_replace( $pattern, '', $config_contents );
		file_put_contents( $config_path, $config_contents );
		echo 'OK';
	} else {
		echo 'NOT FOUND';
	}

	exit;
}
add_action( 'init', 'e2e_remove_wc_remove_data_constant' );

/**
 * http://localhost/?add_wc_remove_all_data
 *
 * @return void
 */
function e2e_add_wc_remove_data_constant() {
	if ( ! isset( $_GET['add_wc_remove_all_data'] ) ) {
		return;
	}

	$config_path = ABSPATH . 'wp-config.php';

	if ( ! is_writable( $config_path ) ) {
		echo 'ERROR: wp-config.php not writable';
		exit;
	}

	$config_contents = file_get_contents( $config_path );

	if ( strpos( $config_contents, 'WC_REMOVE_ALL_DATA' ) !== false ) {
		echo 'OK';
		exit;
	}

	$constant_line = "define('WC_REMOVE_ALL_DATA', true);\n";

	// Add before the line that says "/* That's all, stop editing!"
	$pattern = '/(\/\*\s*That\'s all, stop editing!.*?\*\/)/';

	if ( preg_match( $pattern, $config_contents, $matches ) ) {
		$updated_contents = preg_replace(
			$pattern,
			$constant_line . "\n" . $matches[1],
			$config_contents
		);

		file_put_contents( $config_path, $updated_contents );
		echo 'OK';
	} else {
		echo 'ERROR: Could not find insertion point';
	}

	exit;
}
add_action( 'init', 'e2e_add_wc_remove_data_constant' );

function e2e_setup_woocommerce() {
	if ( ! isset( $_GET['e2e_setup_wc'] ) ) {
		return;
	}

	// Mark setup wizard as completed
	update_option( 'woocommerce_setup_activated', 1 );
	update_option( 'woocommerce_onboarding_profile', [ 'completed' => true ] );
	update_option( 'woocommerce_admin_install_timestamp', time() );
	update_option( 'woocommerce_admin_disabled', 'yes' );

	// Store settings
	update_option( 'woocommerce_store_address', '123 Test St' );
	update_option( 'woocommerce_store_address_2', '' );
	update_option( 'woocommerce_store_city', 'Testville' );
	update_option( 'woocommerce_default_country', 'US:CA' );
	update_option( 'woocommerce_store_postcode', '90001' );
	update_option( 'woocommerce_currency', 'USD' );
	update_option( 'woocommerce_product_type', 'physical' );
	update_option( 'woocommerce_allow_tracking', 'no' );

	// Create pages if not exist
	$page_definitions = [
		'shop'      => [ 'title' => 'Shop', 'content' => '[woocommerce_shop]' ],
		'cart'      => [ 'title' => 'Cart', 'content' => '[woocommerce_cart]' ],
		'checkout'  => [ 'title' => 'Checkout', 'content' => '[woocommerce_checkout]' ],
		'myaccount' => [ 'title' => 'My Account', 'content' => '[woocommerce_my_account]' ],
	];

	foreach ( $page_definitions as $slug => $page ) {
		$page_check = get_page_by_path( $slug );
		if ( ! $page_check ) {
			$page_id = wp_insert_post( [
				'post_title'   => $page['title'],
				'post_name'    => $slug,
				'post_content' => $page['content'],
				'post_status'  => 'publish',
				'post_type'    => 'page',
			] );
			update_option( "woocommerce_{$slug}_page_id", $page_id );
		}
	}

	// Enable Cash on Delivery (COD)
	if ( class_exists( 'WC_Payment_Gateways' ) ) {
		$cod = new WC_Gateway_COD();
		$cod_settings = $cod->settings;
		$cod_settings['enabled'] = 'yes';
		update_option( 'woocommerce_cod_settings', $cod_settings );
	}

	// Enable Flat Rate Shipping in "Rest of the World" zone (Zone ID 0)
	if ( class_exists( 'WC_Shipping_Zones' ) ) {
		$zone = new WC_Shipping_Zone( 0 );
		$methods = $zone->get_shipping_methods();

		foreach ( $methods as $method ) {
			if ( $method->id === 'flat_rate' ) {
				$method->set_enabled( 'yes' );
				$method->set_option( 'cost', '5.00' );
				$method->save();
			}
		}
	}

	echo 'WOOCOMMERCE SETUP COMPLETE';
	exit;
}
add_action( 'init', 'e2e_setup_woocommerce' );


/**
 * Create a page with the WooCommerce checkout shortcode.
 * URL: http://localhost/?e2e_create_wc_checkout_page=1
 *
 * @return void
 */
function e2e_create_wc_checkout_page() {
	if ( ! isset( $_GET['e2e_create_wc_checkout_page'] ) ) {
		return;
	}

	$slug = 'classic-wc-checkout';

	// Check if the page already exists by slug
	$existing_page = get_page_by_path( $slug, OBJECT, 'page' );
	if ( $existing_page ) {
		// Delete the existing page
		wp_delete_post( $existing_page->ID, true );
	}

	// Create new page
	$post_id = wp_insert_post( [
		'post_title'   => 'Classic WC Checkout',
		'post_name'    => $slug,
		'post_status'  => 'publish',
		'post_content' => '[woocommerce_checkout]',
		'post_type'    => 'page',
	] );

	if ( is_wp_error( $post_id ) ) {
		echo 'FAILED TO CREATE PAGE';
	} else {
		echo 'OK';
	}

	exit;
}
add_action( 'init', 'e2e_create_wc_checkout_page' );


/**
 * Delete the classic WooCommerce checkout page if it exists.
 * URL: http://localhost/?e2e_delete_wc_checkout_page=1
 *
 * @return void
 */
function e2e_delete_wc_checkout_page() {
	if ( ! isset( $_GET['e2e_delete_wc_checkout_page'] ) ) {
		return;
	}

	$slug = 'classic-wc-checkout';

	$page = get_page_by_path( $slug, OBJECT, 'page' );
	if ( $page ) {
		wp_delete_post( $page->ID, true );
		echo 'OK';
	} else {
		echo 'NOT FOUND';
	}

	exit;
}
add_action( 'init', 'e2e_delete_wc_checkout_page' );