<?php
/**
 * Bootstrap Activity functionality
 *
 * @package PowerCaptchaReCaptcha/Activity
 */

/**
 * Class PwrcapCaptchaActivityRecord
 *
 * Handles operations related to the captcha activity records table.
 *
 * Provides methods for inserting, retrieving, and deleting records
 * related to captcha activity.
 *
 * @since 1.0.11
 */
class PwrcapCaptchaActivityRecord {
	/**
	 * The name of the database table for captcha activity.
	 *
	 * @var string
	 */
	private static $table_name = 'pwrcap_captcha_activity_records';

	/**
	 * Status constants.
	 */
	const STATUS_FAILED = 'failed';
	const STATUS_SOLVED = 'solved';
	const STATUS_EMPTY  = 'empty';

	/**
	 * Retrieve the full table name, including the WordPress table prefix.
	 *
	 * @return string The full table name.
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . self::$table_name;
	}


	/**
	 * Checks if the plugin's stat record table exists.
	 *
	 * This function queries the database to check whether the specified table exists.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return bool True if table exists, otherwise false.
	 */
	public static function db_table_exists() {
		global $wpdb;

		$table_name = self::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $table_name === $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" );
	}

	/**
	 * Insert a record into the activity records table.
	 *
	 * Clears relevant cache after insertion.
	 *
	 * @param string $captcha_type The type of captcha.
	 * @param string $status       The status (failed, solved, empty).
	 *
	 * @return int|false Insert ID on success, false on failure.
	 */
	public static function insert( $captcha_type, $status ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert(
			self::get_table_name(),
			array(
				'captcha_type' => $captcha_type,
				'status'       => $status,
				'created_at'   => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s' )
		);

		if ( $result ) {
			wp_cache_delete( 'pwrcap_all_records' );
			wp_cache_delete( 'pwrcap_activity_data' );
		}

		return $result;
	}

	/**
	 * Add a record for a solved captcha.
	 *
	 * @param string $captcha_type The type of captcha.
	 *
	 * @return int|false Insert ID on success, false on failure.
	 */
	public static function add_solved_captcha( $captcha_type ) {
		return self::insert( $captcha_type, self::STATUS_SOLVED );
	}

	/**
	 * Add a record for an empty captcha.
	 *
	 * @param string $captcha_type The type of captcha.
	 *
	 * @return int|false Insert ID on success, false on failure.
	 */
	public static function add_empty_captcha( $captcha_type ) {
		return self::insert( $captcha_type, self::STATUS_EMPTY );
	}

	/**
	 * Add a record for a failed captcha.
	 *
	 * @param string $captcha_type The type of captcha.
	 *
	 * @return int|false Insert ID on success, false on failure.
	 */
	public static function add_failed_captcha( $captcha_type ) {
		return self::insert( $captcha_type, self::STATUS_FAILED );
	}

	/**
	 * Get all records from the activity records table.
	 *
	 * Cached for faster access.
	 *
	 * @return array List of records.
	 */
	public static function get_all() {
		$cache_key   = 'pwrcap_all_records';
		$cached_data = wp_cache_get( $cache_key );

		if ( false !== $cached_data ) {
			return $cached_data;
		}

		global $wpdb;

		$table_name = self::get_table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$results = $wpdb->get_results(
			"SELECT * FROM {$table_name}" // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		wp_cache_set( $cache_key, $results );

		return $results;
	}

	/**
	 * Delete a record by ID.
	 *
	 * Clears relevant cache after deletion.
	 *
	 * @param int $id The ID of the record to delete.
	 *
	 * @return int|false Number of rows affected, or false on failure.
	 */
	public static function delete_by_id( $id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->delete(
			self::get_table_name(),
			array( 'id' => $id ),
			array( '%d' )
		);

		if ( $result ) {
			wp_cache_delete( 'pwrcap_all_records' );
			wp_cache_delete( 'pwrcap_activity_data' );
		}

		return $result;
	}

	/**
	 * Get captcha activity data for the last X days (all statuses in one query).
	 *
	 * Cached for faster access. Cache is invalidated when records are added or deleted.
	 *
	 * @param int $days The number of days to fetch.
	 * @return array The captcha activity records grouped by status (solved, failed, empty).
	 */
	public static function get_captcha_activity_data( $days = 7 ) {
		$cache_key   = 'pwrcap_activity_data';
		$cached_data = wp_cache_get( $cache_key );

		if ( false !== $cached_data ) {
			return $cached_data;
		}

		global $wpdb;

		$table_name = self::get_table_name();
		$date_limit = gmdate( 'Y-m-d H:i:s', strtotime( "-$days days" ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$results = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT DATE(created_at) as day, status, COUNT(*) as total FROM {$table_name}
				WHERE created_at >= %s
				GROUP BY day, status
				ORDER BY day DESC",
				$date_limit
			)
		);

		$labels = array();
		for ( $i = $days - 1; $i >= 0; $i-- ) {
			$labels[] = gmdate( 'Y-m-d', strtotime( "-$i days" ) );
		}

		$solved_counts = array_fill( 0, $days, 0 );
		$failed_counts = array_fill( 0, $days, 0 );
		$empty_counts  = array_fill( 0, $days, 0 );

		foreach ( $results as $row ) {
			$index = array_search( $row->day, $labels, true );
			if ( false !== $index ) {
				if ( self::STATUS_SOLVED === $row->status ) {
					$solved_counts[ $index ] = (int) $row->total;
				} elseif ( self::STATUS_FAILED === $row->status ) {
					$failed_counts[ $index ] = (int) $row->total;
				} elseif ( self::STATUS_EMPTY === $row->status ) {
					$empty_counts[ $index ] = (int) $row->total;
				}
			}
		}

		$i18n_labels = array();
		$date_format = get_option( 'date_format' );

		for ( $i = $days - 1; $i >= 0; $i-- ) {
			$timestamp = strtotime( "-$i days" );

			if ( gmdate( 'Y-m-d', $timestamp ) === gmdate( 'Y-m-d' ) ) {
				$i18n_labels[] = __( 'Today', 'power-captcha-recaptcha' );
			} elseif ( gmdate( 'Y-m-d', $timestamp ) === gmdate( 'Y-m-d', strtotime( '-1 day' ) ) ) {
				$i18n_labels[] = __( 'Yesterday', 'power-captcha-recaptcha' );
			} else {
				$i18n_labels[] = date_i18n( $date_format, $timestamp );
			}
		}

		$data = array(
			'labels'        => $i18n_labels,
			'solved_counts' => $solved_counts,
			'failed_counts' => $failed_counts,
			'empty_counts'  => $empty_counts,
		);

		wp_cache_set( $cache_key, $data );

		return $data;
	}
}
