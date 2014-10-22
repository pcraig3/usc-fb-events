<?php
/**
 * USC FB Events
 *
 * @package   USC_FB_Events
 * @author    Paul Craig <pcraig3@uwo.ca>
 * @license   GPL-2.0+
 * @copyright 2014
 *
 * @wordpress-plugin
 * Plugin Name:       USC FB Events
 * Plugin URI:        https://github.com/pcraig3/usc-fb-events
 * Description:       gets events and shows events and modifies events
 * Version:           1.1.2
 * Author:            Paul Craig
 * Author URI:        https://profiles.wordpress.org/pcraig3/
 * Text Domain:       usc-fb-events
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/pcraig3/usc-fb-events
 * GitHub Branch:     master
 * WordPress-Plugin-Boilerplate: v2.6.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'public/DB_API.php');
require_once( plugin_dir_path( __FILE__ ) . 'public/WP_AJAX.php');
require_once( plugin_dir_path( __FILE__ ) . 'public/class-usc-fb-events.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 *
 */
register_activation_hook( __FILE__, array( 'USC_FB_Events', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'USC_FB_Events', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'USC_FB_Events', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

  if ( is_admin() ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-usc-fb-events-admin.php' );
	add_action( 'plugins_loaded', array( 'USC_FB_Events_Admin', 'get_instance' ) );

}

