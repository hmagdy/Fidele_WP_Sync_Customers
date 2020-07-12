<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       test@test.com
 * @since      1.0.0
 *
 * @package    Fidele_Sync_Customers
 * @subpackage Fidele_Sync_Customers/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Fidele_Sync_Customers
 * @subpackage Fidele_Sync_Customers/includes
 * @author     hatem <hatem.said50@gmail.com>
 */
class Fidele_Sync_Customers_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'fidele-sync-customers',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
