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
	 * @since   0.2.1
	 *
	 * @var     string
	 */
	const VERSION = '0.3.0';

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

        add_action( 'init', array( $this, 'register_fbevents_table' ), '1' );
        add_action( 'switch_blog', array( $this, 'register_fbevents_table' ) );

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

        $html_string = '<blockquote id="events">';

        //if there is a 'total'
        if( isset($events_array['total']) ) {

            $total = intval( $events_array['total'] );
        }

        //if the limit is too low, or greater than the total number of returned events
        if ( $limit < 1 || $limit > $total ) {

            //set the max to the total number of events
            $limit = $total;
        }

        //if something goes wrong, then stop the method
        if( $limit < 1 ) {
            //@TODO: write some kind of exception
            return;
        }

        //we redefining it as the indexed 'events' array. losing $events_array['total'] for example
        $events_array = array_reverse($events_array['events']);

        for($i = 0; $i < $total && $limit >= 1; $i++, $limit--) {

            $current_event = $events_array[$i];

            if( isset( $current_event['pic_big'] ) )
                $img_url = esc_url( $current_event['pic_big'] );


            $html_string .= '<a href="' . esc_url( 'http://facebook.com/' . $current_event['eid'] . "/" ) . '" target="_blank">';
            $html_string .= '<div class="events__box flag clearfix">';

            $html_string.= '<div class="flag__image">';

            if($img_url)
                $html_string .= '<img src="' . $img_url . '">';

            $html_string .= '</div>';

            $html_string .= '<div class="flag__body">';
            $html_string .= '<h3 class="alpha" title="' .
                esc_attr( $current_event['host'] . ": " . $current_event['name'] ) .
                esc_attr( $current_event['host'] . ": " . $current_event['name'] ) .
                '">' . esc_html( $current_event['name'] ) . '</h3>';

            $html_string .= '<p class="lede">'
                . esc_html( date("M j", strtotime($current_event['start_time'] ) ) )
                . " | "
                . esc_html( $current_event['host'] )
                . '</p>';

            $html_string .= '</div><!--end of .flag__body-->';
            $html_string .= '<span class="events__box__count">' . (intval( $i ) + 1) . '</span>';
            $html_string .= '</div><!--end of events__box--></a>';

        }

        $html_string .= "</blockquote><!--end of #events-->";

        return $html_string;
    }

    /**
     * Calls some page which calls our Facebook events api
     *
     * @since    0.2.0
     *
     * @return array       at this point, return open Facebook events as an indexed array
     */
    public static function call_api() {

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

        Test_Events::create_fbevents_table();
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    0.2.3
	 */
	private static function single_deactivate() {
		// Define deactivation functionality here

        Test_Events::drop_fbevents_table();
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
     * @since 1.0
     */
    public static function register_fbevents_table() {
        global $wpdb;
        $wpdb->fbevents = "test_fbevents";
    }

    /**
     * Creates our table
     * Hooked onto our single_activate function
     * @since 0.2.3
     */
    private static function create_fbevents_table(){

        global $wpdb;
        global $charset_collate;

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        //Call this manually as we may have missed the init hook
        Test_Events::register_fbevents_table();

            $sql_create_table = "CREATE TABLE {$wpdb->fbevents} (
        eid bigint(20) NOT NULL,
        name varchar(255) NULL,
        start_time datetime NULL,
        host varchar(127) NULL,
        location varchar(255) NULL,
        removed bool NOT NULL default '0',
        PRIMARY KEY  (eid),
        KEY name (name)
        ) $charset_collate; ";

        dbDelta($sql_create_table);

    }

    /**
     * removes our table
     * Hooked onto our single_deactivate function
     * @since 0.2.3
     */
    private static function drop_fbevents_table(){

        global $wpdb;

        $wpdb->fbevents = "test_fbevents";

        $sql = "DROP TABLE IF EXISTS $wpdb->fbevents;";
        $wpdb->query($sql);
    }

    private function get_fbevents_table_columns(){
    return array(
        'eid'=> '%s',
        'name'=> '%s',
        'start_time'=>'%s',
        'host'=>'%s',
        'location'=>'%s',
        'removed'=>'%d',
        );
    }

    /**
     * Inserts a fb_event into the database
     *
     *@param $data array An array of key => value pairs to be inserted
     *@return int The log ID of the created activity log. Or WP_Error or false on failure.
     */
    public static function insert_fbevent( $data=array() ){
        global $wpdb;

        //Set default values
        $data = wp_parse_args($data, array(
            'removed'=> '1',
        ));

        if( ! is_numeric( $data['eid'] ) )
            return false;

        //Check date validity
        if( isset($data['start_time']) ) {
            //Convert activity date from local timestamp to GMT mysql format
            $data['start_time'] = date_i18n( 'Y-m-d H:i:s', $data['start_time'], true );
        }

        //Initialise column format array
        $column_formats = Test_Events::get_fbevents_table_columns();

        //Force fields to lower case
        $data = array_change_key_case ( $data,  CASE_LOWER );

        //White list columns
        $data = array_intersect_key( $data, $column_formats );

        //Reorder $column_formats to match the order of columns given in $data
        $data_keys = array_keys($data);
        $column_formats = array_merge(array_flip($data_keys), $column_formats);

        $wpdb->insert($wpdb->fbevents, $data, $column_formats);

        return $wpdb->insert_id;
    }

    /**
     * Deletes fbevent from 'test_fbevents'
     *
     *@param $eid string (or float) ID of the event to be deleted
     *@return bool Whether the log was successfully deleted.
     */
    public static function delete_fbevent( $eid ){
        global $wpdb;

        //Event ID must be positive integer
        $eid = (float) abs($eid);

        if( empty($eid) )
            return false;

        do_action('delete_fbevent', $eid);

        $sql = $wpdb->prepare("DELETE from {$wpdb->fbevents} WHERE eid = %f", $eid);

        if( !$wpdb->query( $sql ) )
            return false;

        do_action('deleted_fbevent', $eid);

        return true;
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
