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

        $returned_array = $this->filter_events($returned_array);

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

    private function filter_events($event_array) {

        $removed_events_mysql = $this->get_removed_events();
        $eids_of_removed_events = array();

        foreach( $removed_events_mysql as &$event_object ) {

            array_push( $eids_of_removed_events, $event_object->eid );
        }
        unset( $event_object );
        unset( $removed_events_mysql );

        $total = $event_array['total'];
        for($i = 0; $i < $total; $i++) {

            if( in_array( $event_array['events'][$i]['eid'], $eids_of_removed_events ) )
                unset( $event_array['events'][$i] );
        }

        $event_array['events'] = array_values( $event_array['events'] );

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
     *
     * @since   0.4.0 (Stolen from Stephan Harris' excellent examples)
     * https://github.com/stephenh1988/wptuts-user-activity-log/blob/master/wptuts-user-log.php
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
     *
     * @since   0.4.0 (Stolen from Stephan Harris' excellent examples)
     * https://github.com/stephenh1988/wptuts-user-activity-log/blob/master/wptuts-user-log.php
     */
    private static function drop_fbevents_table(){

        global $wpdb;

        $wpdb->fbevents = "test_fbevents";

        $sql = "DROP TABLE IF EXISTS $wpdb->fbevents;";
        $wpdb->query($sql);
    }

    /**
     *
     * @since   0.4.0 (Stolen from Stephan Harris' excellent examples)
     * https://github.com/stephenh1988/wptuts-user-activity-log/blob/master/wptuts-user-log.php
     *
     * @return array and array of columns in our new DB table.  This way we can check queries against them
     */
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
     *
     * @since   0.4.0 (Stolen from Stephan Harris' excellent examples)
     * https://github.com/stephenh1988/wptuts-user-activity-log/blob/master/wptuts-user-log.php
     *
     *@return int The eid of the created fbevent. Or WP_Error or false on failure.
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
        $data = array_change_key_case ( $data, CASE_LOWER );

        //White list columns
        $data = array_intersect_key( $data, $column_formats );

        //Reorder $column_formats to match the order of columns given in $data
        $data_keys = array_keys($data);
        $column_formats = array_merge(array_flip($data_keys), $column_formats);

        return $wpdb->insert($wpdb->fbevents, $data, $column_formats);

        //return $wpdb->insert_id;
    }

    /**
     * Updates an fbevent with supplied data
     *
     *@param $eid  eid of the event to be updated
     *@param $data array An array of column=>value pairs to be updated
     *
     * @since   0.4.0 (Stolen from Stephan Harris' excellent examples)
     * https://github.com/stephenh1988/wptuts-user-activity-log/blob/master/wptuts-user-log.php
     *
     *@return bool Whether the event was successfully updated.
     */
    public static function update_fbevent( $eid, $data=array() ){
        global $wpdb;

        //eid must be numeric
        if( ! is_numeric( $eid ) )
            return false;

        //Check date validity
        if( isset($data['start_time']) ) {
            //Convert activity date from local timestamp to GMT mysql format
            $data['start_time'] = date_i18n( 'Y-m-d H:i:s', $data['start_time'], true );
        }

        //Initialise column format array
        $column_formats = Test_Events::get_fbevents_table_columns();

        //Force fields to lower case
        $data = array_change_key_case ( $data, CASE_LOWER );

        //White list columns
        $data = array_intersect_key($data, $column_formats);

        //Reorder $column_formats to match the order of columns given in $data
        $data_keys = array_keys($data);
        $column_formats = array_merge(array_flip($data_keys), $column_formats);

        if ( false === $wpdb->update($wpdb->fbevents, $data, array('eid'=>$eid), $column_formats) ) {
            return false;
        }

        return true;
    }

    /**
     * Retrieves activity logs from the database matching $query.
     * $query is an array which can contain the following keys:
     *
     * 'fields' - an array of columns to include in returned roles. Or 'count' to count rows. Default: empty (all fields).
     * 'orderby' - eid or start_time. Default: eid.
     * 'order' - asc or desc
     * 'eid' - eid to match, or an array of eids
     * 'since' - timestamp. Return only activities after this date. Default false, no restriction.
     * 'until' - timestamp. Return only activities up to this date. Default false, no restriction.
     *
     *@param array $query Query
     *
     *
     * @since   0.4.0 (Stolen from Stephan Harris' excellent examples)
     * https://github.com/stephenh1988/wptuts-user-activity-log/blob/master/wptuts-user-log.php
     *
     *@return array Array of matching logs. False on error.
     */
    public static function get_fbevents( $query=array() ){
        global $wpdb;

        $fields = array();
        $orderby = "eid";
        $order = "desc";
        $eid = $since = $until = $debug = false;

        /* Parse defaults */
        $defaults = array(
            'fields' => $fields,
            'orderby'=> $orderby,
            'order'=>   $order,
            'eid'=>     $eid,
            'since'=>   $since,
            'until'=>   $until,
        );
        $query = wp_parse_args($query, $defaults);

        /* Form a cache key from the query */
        $cache_key = 'get_fbevents:'.md5( serialize($query));
        $cache = wp_cache_get( $cache_key );
        if ( false !== $cache ) {
            $cache = apply_filters('get_fbevents', $cache, $query);
            return $cache;
        }
        extract($query, EXTR_OVERWRITE);

        /* SQL Select */
        //Whitelist of allowed fields
        $allowed_fields = array_keys(Test_Events::get_fbevents_table_columns());

        if( is_array($fields) ){

            //Convert fields to lowercase (as our column names are all lower case - see part 1)
            $fields = array_map('strtolower',$fields);

            //Sanitize by white listing
            $fields = array_intersect($fields, $allowed_fields);

        }else{
            $fields = strtolower($fields);
        }

        //Return only selected fields. Empty is interpreted as all
        if( empty($fields) ){
            $select_sql = "SELECT* FROM {$wpdb->fbevents}";
        }elseif( 'count' == $fields ) {
            $select_sql = "SELECT COUNT(*) FROM {$wpdb->fbevents}";
        }else{
            $select_sql = "SELECT ".implode(',',$fields)." FROM {$wpdb->fbevents}";
        }

        /*SQL Join */
        //We don't need this, but we'll allow it be filtered (see 'fbevents_clauses' )
        $join_sql='';

        /* SQL Where */
        //Initialise WHERE
        $where_sql = 'WHERE 1=1';
        if( !empty($eid) )
            $where_sql .= $wpdb->prepare(' AND eid=%s', $eid);

        if( !empty($removed) )
            $where_sql .= $wpdb->prepare(' AND removed=%d', $removed);

        if( !empty($append) )
            $where_sql .= $append;

        /*$since = absint($since);
        $until = absint($until);

        if( !empty($since) )
            $where_sql .= $wpdb->prepare(' AND start_date >= %s', date_i18n( 'Y-m-d H:i:s', $since,true));

        if( !empty($until) )
            $where_sql .= $wpdb->prepare(' AND start_date <= %s', date_i18n( 'Y-m-d H:i:s', $until,true));
        */

        /* SQL Order */
        //Whitelist order
        $order = strtoupper($order);
        $order = ( 'ASC' == $order ? 'ASC' : 'DESC' );
        switch( $orderby ){
            case 'eid':
                $order_sql = "ORDER BY eid $order";
                break;
            case 'start_time':
                $order_sql = "ORDER BY start_time $order";
                break;
            default:
                break;
        }

        /* SQL Limit */
        $limit_sql = '';

        /* Filter SQL */
        $pieces = array( 'select_sql', 'join_sql', 'where_sql', 'order_sql', 'limit_sql' );
        $clauses = apply_filters( 'fbevents_clauses', compact( $pieces ), $query );
        foreach ( $pieces as $piece )
            $$piece = isset( $clauses[ $piece ] ) ? $clauses[ $piece ] : '';

        /* Form SQL statement */
        $sql = "$select_sql $where_sql $order_sql $limit_sql";

        if($debug)
            return $sql;

        if( 'count' == $fields ){
            return $wpdb->get_var($sql);
        }

        /* Perform query */
        $events = $wpdb->get_results($sql);

        /* Add to cache and filter */
        wp_cache_add( $cache_key, $events, 24*60*60 );
        $events = apply_filters('get_fbevents', $events, $query);

        return $events;
    }

    /**
     * Deletes fbevent from 'test_fbevents'
     *
     *@param $eid string (or float) ID of the event to be deleted
     *
     *
     * @since   0.4.0 (Stolen from Stephan Harris' excellent examples)
     * https://github.com/stephenh1988/wptuts-user-activity-log/blob/master/wptuts-user-log.php
     *
     *@return bool Whether the fbevent was successfully deleted.
     */
    public static function delete_fbevent( $eid ){
        global $wpdb;

        //eid must be numeric
        if( ! is_numeric( $eid ) )
            return false;

        do_action('delete_fbevent', $eid);

        $sql = $wpdb->prepare("DELETE from {$wpdb->fbevents} WHERE eid = %s", $eid);

        if( !$wpdb->query( $sql ) )
            return false;

        do_action('deleted_fbevent', $eid);

        return true;
    }

    /**
     * Does what it says on the box.  gets removed events (and then applies 'removed' class to list items)
     *
     * @since   0.4.0
     */
    public function get_removed_events() {

        $response = Test_Events::get_fbevents( array (
                'fields' =>     array( 'eid' ),
                'removed' =>    1
            )
        );

        //I want all of the eids
        return $response;
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
