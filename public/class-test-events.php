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
	const VERSION = '0.4.2';

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
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     0.2.0
	 */
	private function __construct() {

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
     * @since    1.1.0
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

        //function returns Facebook events as a json array.
        //in the future, we'll have this take a parameter
        $returned_array = $this->call_api();

        $returned_array = $this->merge_fb_and_db_events($returned_array);
        $returned_array = $this->remove_removed_events($returned_array);

        if( is_array( $returned_array ) ) {

            $parameters = array(

                $returned_array,
                intval($limit),
            );

            $testevents_function = (string) $get . "_" . (string) $show;

            ob_start();

            echo call_user_func_array( array( $this, $testevents_function ), $parameters );

            $result = ob_get_clean();
        }

        if( $result ) {

            return $result;
        }

        return "false";
    }

    /**
     * Function takes event array (returned from Facebook) and removes events that
     * are found in the database.
     *
     * @param array $event_array  an array of events (from Facebook)
     * @return array mixed  an array of events wherein those flagged for removal are absent
     */
    private function remove_removed_events( array $event_array ) {

        $total = $event_array['total'];
        $events = $event_array['events'];

        for( $i = 0; $i < $total; $i++ ) {

            if( isset($events[$i]['removed']) && intval( $events[$i]['removed']) === 1 )
                unset( $events[$i] );
        }

        $event_array['events'] = array_values( $events );
        $event_array['total'] = count( $events );

        return $event_array;
    }

    public function merge_fb_and_db_events( array $event_array ) {

        $all_events_mysql = DB_API::get_fbevents();

        if( ! empty($all_events_mysql) ) {

            $modified_events = array();
            $modified_events['eid'] = array();

            foreach( $all_events_mysql as &$modified_event ) {

                //hacky fun way changes an stdClass into an array
                array_push( $modified_events, json_decode(json_encode($modified_event), true) );
                //create an indexed 'ied' array containing only eids
                array_push( $modified_events['eid'], $modified_event->eid);

            }
            unset( $modified_event );
            unset( $modified_events_mysql );

            $total = $event_array['total'];
            for($i = 0; $i < $total; $i++) {

                //array search (should) return the index of modified_events
                $modified_event_index = array_search( $event_array['events'][$i]['eid'], $modified_events['eid'] );

                if( $modified_event_index !== false ) {

                    $modified_event_array_keys = array_keys( $modified_events[$modified_event_index] );

                    //for every key in the modified event, overwrite the value in the original event
                    foreach( $modified_event_array_keys as &$key ) {

                        //if the modifiable fields are not null
                        if( ! is_null( $modified_events[$modified_event_index][$key] ) ) {

                            //if the key doesn't exist in the old value, just put it in
                            if( ! isset( $event_array['events'][$i][$key] ) )
                                $event_array['events'][$i][$key] = $modified_events[$modified_event_index][$key];

                            //if the modifiable fields are not equal to the current data
                            else if ( $event_array['events'][$i][$key] !== $modified_events[$modified_event_index][$key] ) {
                                $event_array['events'][$i][$key . "_old"] = $event_array['events'][$i][$key];
                                $event_array['events'][$i][$key] = $modified_events[$modified_event_index][$key];
                            }
                        }
                    }

                    unset($modified_event_array_keys);
                }
                unset($key);
            }

        }

        return $event_array;
    }

    /**
     * Return HTML code to list a bunch of Facebook events
     *
     * @param $events_array     an array of events from Facebook
     * @param int $limit        an integer number of events to return.  defaults to the total returned objects.
     *
     * @since    0.2.1
     *
     * @return string           a list of events from Facebook
     */
    private function events_list( $events_array, $limit = 0 ) {

        return require_once('views/in_page_list.php');
    }

    /**
     * Calls some page which calls our Facebook events api
     *
     * @since    0.2.0
     *
     * @return array       at this point, return open Facebook events as an indexed array
     */
    public function call_api() {

        //the url where to get Facebook events
        $ch = curl_init('testwestern.com/api/events/events/2014-04-01');

        curl_setopt( $ch, CURLOPT_HEADER, false ); //TRUE to include the header in the output.
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true ); //TRUE to return transfer as a string instead of outputting it out directly.
        //curl_setopt($ch, CURLOPT_FRESH_CONNECT, true); //TRUE to force the use of a new connection instead of a cached one.

        $returnedString = curl_exec( $ch );
        curl_close( $ch );

        // Define the errors.
        /* $constants = get_defined_constants(true);

        /*$json_errors = array();
        foreach ($constants["json"] as $name => $value) {
            if (!strncmp($name, "JSON_ERROR_", 11)) {
                $json_errors[$value] = $name;
            }
        }

        echo '<h1>';
        echo 'Last error: ', $json_errors[json_last_error()], PHP_EOL, PHP_EOL;
        echo '</h1>';
        die;
        */

        return json_decode( $returnedString, true );
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
     * Store our table name in $wpdb with correct prefix
     * Prefix will vary between sites so hook onto switch_blog too
     *
     * @since   0.4.0 (Stolen from Stephan Harris' excellent examples)
     * https://github.com/stephenh1988/wptuts-user-activity-log/blob/master/wptuts-user-log.php
     *
    public static function register_fbevents_table() {
        global $wpdb;
        $wpdb->fbevents = "test_fbevents";
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
