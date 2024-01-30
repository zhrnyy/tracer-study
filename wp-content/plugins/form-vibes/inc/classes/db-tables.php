<?php

namespace FormVibes\Classes;

/**
 * A utility class for managing the database and tables
 */
class DbTables {


	/**
	 * Runs on activation of the plugin.
	 *
	 * Checks if table exists and if not, creates it.
	 *
	 * @access public
	 * @return void
	 *
	 */
	public static function fv_plugin_activated() {
		// 0.1 default
		// 0.1.1 meta value column data type to text
		// 0.1.2 alter table
		// 0.1.3 user agent,status columns added to entry table
		// 0.1.3 logs table
		// 0.1.5 check table exist, updated option only if all table exist
		// 0.1.6 update Undefined to undefined,
		// 0.1.7 delete status column
		$fv_db_version = '0.1.7';

		if ( get_option( 'fv_db_version' ) !== $fv_db_version ) {
			self::create_db_table();
		}
	}

	/**
	 * Creates the database tables.
	 *
	 * Runs on activation of the plugin.
	 * and creates the database tables.
	 *
	 * @access public
	 * @return void
	 *
	 */
	public static function create_db_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'fv_enteries';

		$wpdb_collate = $wpdb->collate;
		$query        = "CREATE TABLE {$table_name} (
					  `id` int(11) UNIQUE KEY AUTO_INCREMENT NOT NULL,
					  `form_plugin` varchar(50) NOT NULL,
					  `form_id` varchar(100) NOT NULL,
					  `captured` varchar(50) NOT NULL,
					  `captured_gmt` varchar(50) NOT NULL,
					  `url` text NULL,
					  `user_agent` text NULL,
					  `fv_status` varchar(100) NOT NULL DEFAULT 'undefined'
					)collate {$wpdb_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $query, true );

		$table_name = $wpdb->prefix . 'fv_entry_meta';

		$wpdb_collate = $wpdb->collate;
		$query        = "CREATE TABLE {$table_name} (
					  `id` int(11) UNIQUE KEY AUTO_INCREMENT NOT NULL,
					  `data_id` varchar(50) NOT NULL,
					  `meta_key` varchar(100) NOT NULL,
					  `meta_value` text NOT NULL
					)collate {$wpdb_collate}";

		dbDelta( $query, true );

		$table_name = $wpdb->prefix . 'fv_logs';

		$wpdb_collate = $wpdb->collate;
		$query        = "CREATE TABLE {$table_name} (
					  `id` int(11) UNIQUE KEY AUTO_INCREMENT NOT NULL,
					  `event` varchar(50) NOT NULL,
					  `user_id` varchar(20) NOT NULL,
					  `description` text NOT NULL,
					  `export_time` varchar(50) NOT NULL,
					  `export_time_gmt` varchar(50) NOT NULL
					)collate {$wpdb_collate}";

		dbDelta( $query, true );

		$db_version_update = true;

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}fv_enteries'" ) === null ) {
			$db_version_update = false;
		}
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}fv_entry_meta'" ) === null ) {
			$db_version_update = false;
		}
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}fv_logs'" ) === null ) {
			$db_version_update = false;
		}

		if ( $db_version_update ) {
			update_option( 'fv_db_version', '0.1.7' );
		} else {
			update_option( 'fv_db_version', '0' );
		}
	}
}
