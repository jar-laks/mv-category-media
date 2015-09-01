<?php

class MVCategoryFieldsDB {

	const TABLE_NAME = 'mv_category_media';

	public static function get_value( $term_id, $key ) {
		global $wpdb;
		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT meta_value FROM " . self::get_table_name() . " WHERE term_id = %d AND meta_key = %s LIMIT 1", $term_id, $key ), ARRAY_A
		);

		if ( is_array( $row ) ) {
			$value = maybe_unserialize( $row['meta_value'] );
		} else {
			$value = false;
		}

		return $value;

	}

	public static function delete_term_meta( $term_id ) {
		global $wpdb;
		$wpdb->query(
			$wpdb->prepare( "DELETE FROM " . self::get_table_name() . " WHERE term_id = %d", $term_id )
		);
	}

	public static function get_table_name() {
		global $wpdb;

		return $wpdb->prefix . self::TABLE_NAME;
	}


	public static function save_value( $term_id, $key, $value ) {
		global $wpdb;
		$value = maybe_serialize( $value );
		$old   = self::get_value( $term_id, $key );
		if ( $old === false ) {
			return $wpdb->insert( self::get_table_name(), array(
				'term_id'    => $term_id,
				'meta_key'   => $key,
				'meta_value' => $value
			) );
		} else {
			return $wpdb->update( self::get_table_name(), array( 'meta_value' => $value ), array(
				'term_id'  => $term_id,
				'meta_key' => $key
			) );
		}

	}


	public static
	function create_db_table() {
		global $wpdb;
		if ( $wpdb->get_var( "SHOW TABLES LIKE '" . self::get_table_name() . "';" ) != self::get_table_name() ) {

			$query = "CREATE TABLE `" . self::get_table_name() . "` (
	  `id` bigint(10) NOT NULL AUTO_INCREMENT,
	  `term_id` bigint(10) NOT NULL DEFAULT '0',
	  `meta_key` VARCHAR(1000),
	  `meta_value` longtext,
	    PRIMARY KEY (`id`),
	            KEY `term_id` (`term_id`),
				KEY `meta_key` (`meta_key`)
	) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $query );
		}

	}

	public static function drop_table() {
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			exit();
		}
		global $wpdb;
		$wpdb->query( "DROP TABLE IF EXISTS " . self::get_table_name() . ";" );
	}

}