<?php
/**
 * Test Events.
 *
 * @package   Test_Events
 * @author    Paul Craig <pcraig3@uwo.ca>
 * @license   GPL-2.0+
 * @copyright 2014
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * @package Test_Events
 * @author  Paul Craig <pcraig3@uwo.ca>
 */
class Test_Events {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   0.1.0
	 *
	 * @var     string
	 */
	const VERSION = '0.9.3';

	/*
	 * Unique identifier for your plugin.
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    0.1.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'test-events';

	/**
	 * Instance of this class.
	 *
	 * @since    0.1.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

    /**
     * instance of the WP_AJAX class.
     * registers and contains all of the WordPress AJAX methods
     *
     * @since    0.9.2
     *
     * @var     object
     */
    public $wp_ajax = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
     * ALSO queue up our horrible AJAX methods.
	 *
	 * @since     0.9.1
	 */
	private function __construct() {

        $this->wp_ajax = WP_AJAX::get_instance();

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		/* Define custom functionality.
		 * Refer To http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		//add_action( '@TODO', array( $this, 'action_method_name' ) );
		//add_filter( '@TODO', array( $this, 'filter_method_name' ) );

        add_shortcode( 'testevents', array( $this, 'testplugin_func') );

        add_action( 'init', 'DB_API::register_fbevents_table', '1' );
        add_action( 'switch_blog', 'DB_API::register_fbevents_table' );

    }

    /**
     * Function meant to target the [testplugin] shortcode.  Grabs the attributes in the shortcode to
     * call a function somewhere down there.
     *
     * @param $atts         create an associative array based on attributes and values in the shortcode
     *
     * @since    0.9.0
     *
     * @return string       a complimentary adjective for students
     */
    public function testplugin_func ( $atts ) {

        //initialize your variables
        $get = $show = $limit = $result = false;

        extract(
            shortcode_atts(
                array(
                    'get'   => 'events',
                    'show'  => 'count',
                    'limit'   => 10,
                ), $atts ),
            EXTR_OVERWRITE);

        //if( is_array( $returned_array ) ) {

            $parameters = array(

                intval($limit),
            );

            $testevents_function = (string) $get . "_" . (string) $show;

            ob_start();

            /* @TODO: Explain yourself. */
            echo call_user_func_array( array( $this, $testevents_function ), $parameters );

            $result = ob_get_clean();
        //}

        if( $result ) {

            return $result;
        }

        return "false";
    }

    /**
     * Points to the file that generates and returns HTML code to list a bunch of Facebook events
     *
     * @since    0.4.0
     *
     * @param int $limit            an integer number of events to return.  defaults to the total returned objects.     *
     *
     * @return string           a list of events from Facebook
     */
    private function events_list( $limit = 0 ) {

        //function returns Facebook events as a json array.
        //in the future, we'll have this take a parameter
        $events_array = $this->wp_ajax->call_api();

        $events_array = $this->wp_ajax->facebook_urls($events_array);
        $events_array = $this->wp_ajax->merge_fb_and_db_events($events_array);

        $events_array = $this->wp_ajax->remove_removed_events($events_array);
        $events_array = $this->wp_ajax->date_strings_to_timestamps($events_array);

        return require_once('views/in-page-list.php');
    }

    /**
     * Build the Event list brought in by Ajax with filter.js applied to them
     * Queues up the relevant .js files to get it going.
     *
     * @since    0.9.3
     */
    private function events_ajax() {

        //add_action( 'wp_head', array( $this, 'add_ajax_library' ) );


        wp_enqueue_script( 'tinysort', plugins_url( '../admin/assets/js/jquery.tinysort.min.js', __FILE__ ), array( 'jquery' ), self::VERSION );
        wp_enqueue_script( 'filterjs', plugins_url( '../admin/assets/js/filter.js', __FILE__ ), array( 'jquery', 'tinysort', 'jquery-ui-core' ), self::VERSION );
        wp_enqueue_script( 'public_filterjs', plugins_url( '/assets/js/public-filter.js', __FILE__ ), array( 'jquery', 'tinysort', 'jquery-ui-core', 'filterjs' ), self::VERSION );

        // declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
        wp_localize_script( 'public_filterjs', "ajax", array( 'url' => admin_url( 'admin-ajax.php' ) ) );

        return require_once('views/ajax-list.php');
    }

    /**
     * Build the Event list brought in by Ajax with filter.js applied to them
     * Queues up the relevant .js files to get it going.
     *
     * @since    0.9.3
     */
    private function events_widget() {

        wp_enqueue_script( 'public_widgetjs', plugins_url( '/assets/js/public-widget.js', __FILE__ ), array( 'jquery', 'jquery-ui-core' ), self::VERSION );

        // declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
        wp_localize_script( 'public_widgetjs', "ajax", array( 'url' => admin_url( 'admin-ajax.php' ) ) );

        return require_once('views/widget-list.php');
    }

    /**
	 * Return the plugin slug.
	 *
	 * @since    0.1.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     0.1.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    0.1.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    0.1.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    0.1.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    0.1.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    0.2.3
	 */
	private static function single_activate() {
		// Define activation functionality here

        DB_API::create_fbevents_table();
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    0.2.3
	 */
	private static function single_deactivate() {
		// Define deactivation functionality here

        //@TODO: stop removing table on deactivate.
        DB_API::drop_fbevents_table();
    }

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    0.1.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    0.1.0
	 */
	public function enqueue_scripts() {
        wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	}

	/**
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    0.1.0
	 */
	public function action_method_name() {
		// Define your action hook callback here
	}

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *        Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    0.1.0
	 */
	public function filter_method_name() {
		// Define your filter hook callback here
	}

}
