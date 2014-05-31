<?php
/**
 * The WordPress Plugin Boilerplate.
 *
 *
 * @package   Test_Events
 * @author    Paul Craig <pcraig3@uwo.ca>
 * @license   GPL-2.0+
 * @copyright 2014
 *
 * @wordpress-plugin
 * Plugin Name:       Test Events
 * Plugin URI:        http://testwestern.com
 * Description:       gets events and shows events
 * Version:           0.0.1
 * Author:            Paul Craig
 * Text Domain:       test-events
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/<owner>/<repo>
 * WordPress-Plugin-Boilerplate: v2.6.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'public/class-test-events.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *
 */
register_activation_hook( __FILE__, array( 'Test_Events', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Test_Events', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Test_Events', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 *
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-test-events-admin.php' );
	add_action( 'plugins_loaded', array( 'Test_Events_Admin', 'get_instance' ) );

}