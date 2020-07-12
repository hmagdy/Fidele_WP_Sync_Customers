<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://fidele.vip/
 * @since             1.0.0
 * @package           Fidele_Sync_Customers
 *
 * @wordpress-plugin
 * Plugin Name:       Fidele sync customers
 * Plugin URI:        https://fidele.vip/
 * Description:       Fidele plugin to sync customers
 * Version:           1.0.0
 * Author:            proCrew
 * Author URI:        http://www.procrew.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       fidele-sync-customers
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'FIDELE_SYNC_CUSTOMERS_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-fidele-sync-customers-activator.php
 */
function activate_fidele_sync_customers() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-fidele-sync-customers-activator.php';
	Fidele_Sync_Customers_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-fidele-sync-customers-deactivator.php
 */
function deactivate_fidele_sync_customers() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-fidele-sync-customers-deactivator.php';
	Fidele_Sync_Customers_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_fidele_sync_customers' );
register_deactivation_hook( __FILE__, 'deactivate_fidele_sync_customers' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-fidele-sync-customers.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_fidele_sync_customers() {

	$plugin = new Fidele_Sync_Customers();
	$plugin->run();
}

run_fidele_sync_customers();


