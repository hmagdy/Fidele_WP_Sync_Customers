<?php

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
/**
 * Fired during plugin activation
 *
 * @link       
 * @since      1.0.0
 *
 * @package    Fidele_Sync_Customers
 * @subpackage Fidele_Sync_Customers/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Fidele_Sync_Customers
 * @subpackage Fidele_Sync_Customers/includes
 * @author     hatem <hatem.said50@gmail.com>
 */
class Fidele_Sync_Customers_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $table_name = 'fidele';

        $sql = "CREATE TABLE $table_name (email text NOT NULL, password text NOT NULL) $charset_collate;";

        dbDelta( $sql );
	}

}
