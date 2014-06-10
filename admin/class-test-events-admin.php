<?php
/**
 * Test Events.
 *
 * @package   Test_Events_Admin
 * @author    Paul Craig <pcraig3@uwo.ca>
 * @license   GPL-2.0+
 * @copyright 2014
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-test-events.php`
 *
 * @package Test_Events_Admin
 * @author  Paul Craig <pcraig3@uwo.ca>
 */
class Test_Events_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    0.1.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    0.1.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     0.1.0
	 */
	private function __construct() {

        add_action("wp_ajax_return_to_or_remove_from_calendar", array( $this, "return_to_or_remove_from_calendar" ) );
        add_action("wp_ajax_nopriv_return_to_or_remove_from_calendar", array( $this, "login_please") );

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		/*
		 * Call $plugin_slug from public plugin class.
		 */
		$plugin = Test_Events::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

		/*
		 * Define custom functionality.
		 *
		 * Read more about actions and filters:
		 * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		//add_action( '@TODO', array( $this, 'action_method_name' ) );
		//add_filter( '@TODO', array( $this, 'filter_method_name' ) );
        //$this->add_plugin_admin_menu();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     0.1.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		/*
		 * @TODO :
		 *
		 * - Uncomment following lines if the admin class should only be available for super admins
		 */
		/* if( ! is_super_admin() ) {
			return;
		} */

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     0.1.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), Test_Events::VERSION );
		}

	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     0.3.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {

		if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( $this->plugin_screen_hook_suffix == $screen->id ) {
            wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery', 'simple_filterjs' ), Test_Events::VERSION );
            wp_enqueue_script( 'tinysort', plugins_url( 'assets/js/jquery.tinysort.min.js', __FILE__ ), array( 'jquery' ), Test_Events::VERSION );
            wp_enqueue_script( 'filterjs', plugins_url( 'assets/js/filter.js', __FILE__ ), array( 'jquery', 'tinysort', 'jquery-ui-core' ), Test_Events::VERSION );
            wp_enqueue_script( 'simple_filterjs', plugins_url( 'assets/js/simple_filter.js', __FILE__ ), array( 'jquery', 'tinysort', 'jquery-ui-core', 'filterjs' ), Test_Events::VERSION );
		}

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    0.1.0
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		 *
		 * @TODO:
		 *
		 * - Change 'manage_options' to the capability you see fit
		 *   For reference: http://codex.wordpress.org/Roles_and_Capabilities
		 */
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Test Events Settings', $this->plugin_slug ),
			__( 'Test Events Settings', $this->plugin_slug ),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'display_plugin_admin_page' )
		);

        /* Plugin Name: Admin Page Framework Tutorial 01 - Create an Admin Page *
        if ( ! class_exists( 'AdminPageFramework' ) ) {

            include_once( dirname( dirname( dirname( __FILE__ ) ) ) . '/admin-page-framework/library/admin-page-framework.min.php' );
        }

        include_once( dirname( __FILE__ ) . '/views/apf-settings.php' );

        // Instantiate the class object.
        $sp = new APF_SettingsPage("test_events");
        $sp->setFooterInfoLeft( '<br />Custom Text on the left hand side.', false );
        */
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    0.1.0
	 */
	public function display_plugin_admin_page() {
        include_once( 'views/admin.php' );
        include_once( 'ajax/ajax-test-events-admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    0.1.0
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);

	}

	/**
	 * NOTE:     Actions are points in the execution of a page or process
	 *           lifecycle that WordPress fires.
	 *
	 *           Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    0.1.0
	 */
	public function action_method_name() {
		// Define your action hook callback here
	}

	/**
	 * NOTE:     Filters are points of execution in which WordPress modifies data
	 *           before saving it or sending it to the browser.
	 *
	 *           Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *           Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    0.1.0
	 */
	public function filter_method_name() {
		// Define your filter hook callback here
	}


    /**
     * This function right here is executed when, in the "manage events" menu, someone
     * clicks the "Remove From/Return To Calendar" buttons.
     * The event is then either removed from or returned to 'test_fbevents'
     * Based on tutorial here:
     * http://wp.smashingmagazine.com/2011/10/18/how-to-use-ajax-in-wordpress/
     *
     * @return 		returns an encoded JSON string
     */
    public function return_to_or_remove_from_calendar() {

        $button_id = $_POST['button_id'];

        if ( !wp_verify_nonce( $_POST['nonce'], $button_id . "_nonce")) {
            exit("No naughty business please");
        }

        global $wpdb;

        //we want the id, the name, the host, and the start time
        $eid = 		$_POST['eid'];
        $name = 		$_POST['name'];
        //$host = 		$_POST['host'];
        //$start_time =   date_i18n( 'Y-m-d H:i:s', $_POST['start_time'], true ); //convert the unix timestamp to a string that SQL understands
        $response = 	false;

        if($button_id === 'remove_event_button')
            $response = $this->remove_from_calendar_query($wpdb, $eid);
        if($button_id === 'display_event_button')
            $response = $this->return_to_calendar_query($wpdb, $eid);

        if($response === false) {
            $result['success'] = false;
        }
        else {
            $result['success'] = true;
            $result['name'] = $name;
        }

        //this is meant to decide what to do whether the call was made from a browser, or if JS is enabled.
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode($result);
        }
        else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }

        die();
    }

    /**
     * Pretty straightforward.  Add an event to the 'tblCalendarRemovedEvents' table.
     * Any events in this table will not show up on the public-facing USC calendar.
     *
     * @param         $wpdb  the global wbdb.  Facilitates database calls.
     * @param  string $eid    Facebook Id for an event
     * @return 			     true if mysql query successful.  false if failed.
     */
    public function remove_from_calendar_query($wpdb, $eid) {

        return $wpdb->query( $wpdb->prepare(
            "
                INSERT INTO test_fbevents
                (eid, removed)
                VALUES (%f, %d)
                ON DUPLICATE KEY UPDATE
                removed = VALUES(removed)
            ",
            $eid,
            1
        ) );
    }

    /**
     * Pretty straightforward.  Delete an event from the 'tblCalendarRemovedEvents' table.
     * Events not present in this table will show up on the public-facing USC calendar.
     *
     * @param         $wpdb  the global wbdb.  Facilitates database calls.
     * @param  string $eid    Facebook Id for an event
     * @return 			     true if mysql query successful.  false if failed.
     */
    public function return_to_calendar_query($wpdb, $eid) {

        return $wpdb->query( $wpdb->prepare(
            "
                INSERT INTO test_fbevents
                (eid, removed)
                VALUES (%f, %d)
                ON DUPLICATE KEY UPDATE
                removed = VALUES(removed)
            ",
            $eid,
            0
        ) );

        //@TODO: if everyting else equals null, delete
        /*return $wpdb->query( $wpdb->prepare(
            "
             DELETE FROM tblCalendarRemovedEvents
             WHERE id = %f
            ",
            $id
        ) );*/
    }

    /**
     * Bare-bones method that rejects non-logged-in users.  Used for all ajax methods.
     * @return 		echoes a string telling non-logged in users to log in.
     */
    public function login_please() {
        echo "Log in before you do that, you cheeky monkey.";
        die();
    }


}
