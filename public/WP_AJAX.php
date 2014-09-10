<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 18/06/14
 * Time: 11:05 AM
 */

namespace USC_FB_Events;

class WP_AJAX {

    /**
     * Instance of this class.
     *
     * @since    0.9.2
     *
     * @var      object
     */
    protected static $instance  = null;

    /**
     * today: 1407456001
     *
     * Feb, 2014:   1391212801
     * March, 2014: 1393632001
     * April, 2014: 1396310401
     *
     * 2015: 1420070401
     * 2014: 1388534401
     * 2013: 1356998401
     * 2012: 1325376001
     */
    //set some default starts and ends, so that we can call our API without parameters
    public $start        = null;
    public $end          = null;
    public $expiration   = null;

    /**
     * @var bool    for saving the default $wp_using_ext_object_cache so as not to bugger up our plugin
     *
     * @since 0.9.2
     */
    private $wp_using_ext_object_cache_status;

    /**
     * @var string  for saving the default server timezone before we do any date manipulations for events
     *
     * @since 0.9.9
     *
     */
    private $date_default_timezone_get_status;

    private function __construct() {

        add_action("wp_ajax_return_to_or_remove_from_calendar", array( $this, "return_to_or_remove_from_calendar" ) );
        add_action("wp_ajax_nopriv_return_to_or_remove_from_calendar", array( $this, "login_please") );

        add_action("wp_ajax_get_events", array( $this, "get_events" ) );
        add_action("wp_ajax_nopriv_get_events", array( $this, "get_events") );

        add_action("wp_ajax_update_wordpress_transient_cache", array( $this, "update_wordpress_transient_cache" ) );
        add_action("wp_ajax_nopriv_update_wordpress_transient_cache", array( $this, "update_wordpress_transient_cache") );

        $this->start = 1396310401;

        //seems like a reasonable limit
        $this->end = $this->start + (YEAR_IN_SECONDS * 2);

        $this->expiration = WEEK_IN_SECONDS;

        //set a default value to the object cache whatever so that we don't accidentally call the wrong method before it's set
        global $_wp_using_ext_object_cache;
        $this->wp_using_ext_object_cache_status = $_wp_using_ext_object_cache;

        //set a default timezone so that we don't accidentally call the wrong method first and overwrite anything
        $this->date_default_timezone_get_status = date_default_timezone_get();
    }

    /**
     * Return an instance of this class.
     *
     * @since     0.9.2
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
     * @since    0.9.9
     */
    public function turn_off_object_cache_so_our_bloody_plugin_works() {

        global $_wp_using_ext_object_cache;

        $this->wp_using_ext_object_cache_status = $_wp_using_ext_object_cache;

        $_wp_using_ext_object_cache = false;
    }
    /**
     * @since    0.9.9
     */
    public function turn_object_caching_back_on_for_the_next_poor_sod() {

        global $_wp_using_ext_object_cache;

        $_wp_using_ext_object_cache = $this->wp_using_ext_object_cache_status;
    }

    /**
     * @since 0.8.3
     */
    public function set_server_to_local_time() {
        $this->date_default_timezone_get_status = date_default_timezone_get();
        date_default_timezone_set("America/Toronto");
    }
    /**
     * @since 0.8.3
     */
    public function set_server_back_to_default_time() {
        date_default_timezone_set($this->date_default_timezone_get_status);
    }


    /**
     * This function right here is executed when, in the "manage events" menu, someone
     * clicks the "Remove From/Return To Calendar" buttons.
     * The event is then either removed from or returned to 'usc_fb_events'
     * Based on tutorial here:
     * http://wp.smashingmagazine.com/2011/10/18/how-to-use-ajax-in-wordpress/
     *
     * @since    0.9.7
     *
     * @return 		returns an encoded JSON string
     */
    public function return_to_or_remove_from_calendar() {

        $button_id = $_POST['button_id'];

        $this->make_sure_the_nonce_checks_out( $button_id, $_POST['nonce'] );

        //we want the id, the name, the host, and the start time
        $eid = 		$_POST['eid'];
        $name = 		$_POST['name'];
        //$start_time =   date_i18n( 'Y-m-d H:i:s', $_POST['start_time'], true ); //convert the unix timestamp to a string that SQL understands
        $response = 	false;

        if($button_id === 'remove_event_button') {

            $response = DB_API::insert_on_duplicate_key_update(
                $eid,
                array(
                    'removed' =>    1,
                    'name' =>       $name
                ));

        }
        if($button_id === 'display_event_button') {
            $fbevent_exists_unmodified_in_db = DB_API::get_unmodified_event_count_by_eid( $eid );

            $response = ( $fbevent_exists_unmodified_in_db ) ?
                DB_API::delete_fbevent( $eid ) :
                DB_API::update_fbevent( $eid, array( 'removed' => 0) );
        }

        if($response === false) {
            $result['success'] = false;
            $result['response'] = $response;
        }
        else {
            $result['success'] = true;
            $result['name'] = $name;
            $result['response'] = $response;
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
     * Does (a bit more than) what it says on the box.  gets all facebook and db events (and then merges their values)
     * and then returns everything to the javascript function waiting for it.
     *
     * @since    0.9.8
     */
    public function get_events() {

        $this->make_sure_the_nonce_checks_out( $_POST['attr_id'], $_POST['nonce'] );


        $whitelist      = ( isset($_POST['whitelist'] ) )       ? $_POST['whitelist']       : false;
        $remove_events  = ( isset($_POST['remove_events']) )    ? $_POST['remove_events']   : false;
        $start          = ( isset($_POST['start'] ) )           ? $_POST['start']           : $this->start;
        $end            = ( isset($_POST['end'] ) )             ? $_POST['end']             : $start + (YEAR_IN_SECONDS * 2);
        $calendars      = ( isset($_POST['calendars'] ) )       ? $_POST['calendars']       : '';
        $limit          = ( isset($_POST['limit'] ) )           ? $_POST['limit']           : 0;


        $transient_name = $this->generate_transient_name( $start, $end, $calendars, $limit );
        $events_stored_in_cache = $this->if_stored_in_wordpress_transient_cache( $transient_name );

        if( false === $events_stored_in_cache ) {

            $response = $this->call_events_api( $start, $end, $calendars, $limit );
            $events_stored_in_cache = false;

        } else {

            $response = $events_stored_in_cache;
            $events_stored_in_cache = true;
        }

        if($whitelist)
            $response['events'] = DB_API::whitelist_array_items($response['events']);

        $response = $this->merge_fb_and_db_events($response);

        if($remove_events)
            $response = $this->remove_removed_events($response);

        //javascript CANNOT understand dates
        $response = $this->date_strings_to_timestamps($response);

        $result['if_cached']        = $events_stored_in_cache;
        $result['transient_name']   = $transient_name;

        $result['start']        = $start;
        $result['end']          = $end;
        $result['calendars']    = $calendars;
        $result['limit']        = $limit;

        $result['response'] = $response;
        $result['success']  = ( false !== $response ) ? true : false;

        echo json_encode($result);
        die();

    }

    /**
     * This method implements our WordPress caching system.  Basically, instead of calling (@see) call_events_api all the time,
     * we can store its value to the cache (removing the old value first).  So that the next time it's called, we might just
     * get it from the WP_database instead of going all the way to Facebook for the data.
     *
     * The assumption here is that we've run (@see) get_events first, and then, if a non-cached value is returned to the front-end
     * JS, then this method will be called next (via AJAX), and the next time get_events is called, it quickly returns the
     * cached value.
     *
     * @since    0.9.9
     *
     */
    public function update_wordpress_transient_cache() {

        // Ignore user abort.  We want the cache updated whether or not a user is still on the page.
        ignore_user_abort(true);

        $this->make_sure_the_nonce_checks_out( $_POST['attr_id'], $_POST['nonce'] );


        $transient_name = ( isset($_POST['transient_name'] ) )  ? $_POST['transient_name']  : 'call_events_api_generic';
        $start          = ( isset($_POST['start'] ) )           ? $_POST['start']           : $this->start;
        $end            = ( isset($_POST['end'] ) )             ? $_POST['end']             : $start + (YEAR_IN_SECONDS * 2);
        $calendars      = ( isset($_POST['calendars'] ) )       ? $_POST['calendars']       : '';
        $limit          = ( isset($_POST['limit'] ) )           ? $_POST['limit']           : 0;

        $json_decoded_events_array = $this->call_events_api( $start, $end, $calendars, $limit);
        $expiration = $this->expiration;

        $this->turn_off_object_cache_so_our_bloody_plugin_works();

        delete_site_transient( $transient_name );

        $if_transient_set = set_site_transient( $transient_name, json_encode($json_decoded_events_array), $expiration );

        $this->turn_object_caching_back_on_for_the_next_poor_sod();

        $result['success'] = $if_transient_set;

        echo json_encode( $result );
        die();
    }

    /**
     * Bare-bones method that rejects non-logged-in users.  Used for all ajax methods.
     *
     * @since   0.4.0
     *
     * @return 		echoes a string telling non-logged in users to log in.
     */
    public function login_please() {
        echo "Log in before you do that, you cheeky monkey.";
        die();
    }

    /** NON AJAX STUFF  **/

    /**
     * Method that abstracts the nonce-checking.  It's not actually that interesting.
     * If any of the values aren't set (or are wrong), execution halts.
     *
     * @since    0.9.7
     *
     * @param string $attr_id   all of my nonce names end with "_nonce" and so we need the prefix
     * @param string $nonce     the nonce itself
     */
    private function make_sure_the_nonce_checks_out( $attr_id = "", $nonce = "") {

        if ( ! wp_verify_nonce( $nonce, $attr_id . "_nonce") )
            exit("No naughty business please");

    }

    /**
     * @param array $event_array  an array of events (from Facebook)
     *
     * @since    0.9.0
     *
     * @return array mixed  an array of events with Facebook urls added
     */
    public function date_strings_to_timestamps( array $event_array ) {

        $this->set_server_to_local_time();

        $total = $event_array['total'];
        $events = $event_array['events'];

        for( $i = 0; $i < $total; $i++ ) {

            foreach($events[$i] as $key => $value) {

                //if it starts with
                if( strpos($key, "start_time") === 0 ) {
                    $events[$i][$key] = strtotime($events[$i][$key]);
                }
            }
        }

        $this->set_server_back_to_default_time();

        $event_array['events'] = $events;

        /*
        echo "<pre>";
        var_dump($event_array['events'] );
        echo "</pre>";

        die();
         */

        return $event_array;
    }

    /**
     * Super handy function merges our DB event data with FB events.
     * Any conflicting values have their keys suffixed with "_fb".
     * ie, Facebook's "host" becomes "host_fb" if I have a "host" of my own.
     *
     * @param array $event_array
     *
     * @since    0.5.1
     *
     * @return array
     */
    public function merge_fb_and_db_events( array $event_array ) {

        $all_db_events_mysql = DB_API::get_fb_events();

        if( ! empty($all_db_events_mysql) ) {

            $all_db_events = array();
            $all_db_events['eid'] = array();

            foreach( $all_db_events_mysql as &$db_event ) {

                //hacky fun way changes an stdClass into an array
                array_push( $all_db_events, json_decode(json_encode($db_event), true) );
                //create an indexed 'ied' array containing only eids
                array_push( $all_db_events['eid'], $db_event->eid);

            }
            unset( $db_event );
            unset( $all_db_events_mysql );

            $total = $event_array['total'];
            for($i = 0; $i < $total; $i++) {

                //array search (should) return the index of db_events
                $db_event_index = array_search( $event_array['events'][$i]['eid'], $all_db_events['eid'] );

                if( $db_event_index !== false ) {

                    $all_db_event_array_keys = array_keys( $all_db_events[$db_event_index] );

                    //for every key in the db_event, overwrite the value in the original event
                    foreach( $all_db_event_array_keys as &$key ) {

                        //if the modifiable fields are not EMPTY -- WE CAN'T F*CKING PUT 'NULL' INTO UPDATE STATEMENTS
                        if( ! empty( $all_db_events[$db_event_index][$key] ) && $all_db_events[$db_event_index][$key] !== "0000-00-00 00:00:00" ) {

                            //if the key doesn't exist in the old value, just put it in
                            if( ! isset( $event_array['events'][$i][$key] ) )
                                $event_array['events'][$i][$key] = $all_db_events[$db_event_index][$key];

                            //if the modifiable fields are not equal to the current data
                            else if ( $event_array['events'][$i][$key] !== $all_db_events[$db_event_index][$key] ) {
                                $event_array['events'][$i][$key . "_fb"] = $event_array['events'][$i][$key];
                                $event_array['events'][$i][$key] = $all_db_events[$db_event_index][$key];
                            }
                        }
                    }

                    unset($all_db_event_array_keys);
                }
                unset($key);
            }

        }

        $sort_criteria =
            array('start_time' => array(SORT_DESC, SORT_STRING),
            );

        $event_array['events'] = $this->multisort($event_array['events'], $sort_criteria, true);

        return $event_array;
    }

    /**
     * Function takes merged event array and purges if of events flagged "removed"
     *
     * @param array $event_array  an array of events (from Facebook)
     *
     * @since    0.5.0
     *
     * @return array mixed  an array of events wherein those flagged for removal are removed
     */
    public function remove_removed_events( array $event_array ) {

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

    /**
     * Multisort function sorts two-dimensional arrays on specific keys.
     * Ripped off the PHP reference page from one of the comments.
     * http://www.php.net/manual/en/function.array-multisort.php#114076
     *
     * @author Robert C
     * C probably short for "Champ"
     *
     * @param $data                 the array to be sorted
     * @param $sortCriteria         array of selected keys and how to sort them
     * @param bool $caseInSensitive whether or not to sort stings by case
     *
     * @since    0.5.0
     *
     * @return bool|mixed           returns your array sorted by whatever the eff you asked for
     */
    public function multisort($data, $sortCriteria, $caseInSensitive = true)
    {
        if( !is_array($data) || !is_array($sortCriteria))
            return false;
        $args = array();
        $i = 0;
        foreach($sortCriteria as $sortColumn => $sortAttributes)
        {
            $colList = array();
            foreach ($data as $key => $row)
            {
                $convertToLower = $caseInSensitive && (in_array(SORT_STRING, $sortAttributes) || in_array(SORT_REGULAR, $sortAttributes));
                $rowData = $convertToLower ? strtolower($row[$sortColumn]) : $row[$sortColumn];
                $colLists[$sortColumn][$key] = $rowData;
            }
            $args[] = &$colLists[$sortColumn];

            foreach($sortAttributes as $sortAttribute)
            {
                $tmp[$i] = $sortAttribute;
                $args[] = &$tmp[$i];
                $i++;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return end($args);
    }

    /**
     * In order to cache a result properly, we need to make sure that only that particular API call can access it.
     * Further, the things that can affect your API calls are the start/end times, the calendars you want, and the
     * event limits you ask for.  This method, then, creates transient names based on api calls so that new calls
     * call Facebook but reused calls just get the (much faster queried) transient data.
     *
     * @since       0.9.8
     *
     * @param $start            a unix timestamp, get all events from facebook starting after this time
     * @param $end              a unix timestamp, get all events from facebook starting before this time
     * @param $calendars        names of text files on the server
     * @param $limit            limit events retrieved to this number or less
     * @return string|WP_Error  a shiny new name for our transient
     */
    public function generate_transient_name( $start, $end, $calendars, $limit) {

        $transient_name = '';

        /**
         * Okay, so here's the algorithm I worked out earlier
         * Basically, transients have to be different if we have a different
         *  start
         *  end
         *  calendars
         *  limit
         *
         * so use a bit of each of them to make your string.  easy.
         *
         * transient names can be a maximum of 40 characters, so I'm limiting every field to 6 characters
         * so there it is.  pick (up to) six characters from every value
         */
        $char_limit = 6;

        $start   = number_format( $start , 0 , '', '' );
        $start_string   = ( strlen( $start ) >= $char_limit ) ? substr( $start, -$char_limit ) : $start;

        $end     = number_format( $end , 0 , '', '' );
        $end_string   = ( strlen( $end ) >= $char_limit ) ? substr( $end, -$char_limit ) : $end;

        $calendars_string = $this->generate_calendar_string_for_transient_cache( $calendars, $char_limit );

        $limit   = number_format( $limit , 0 , '', '' );
        $limit_string   = ( strlen( $limit ) >= $char_limit ) ? substr( $limit, -$char_limit ) : $limit;


        $transient_name = "s" . $start_string . "_e" . $end_string . "_c" . $calendars_string . "_l" . $limit_string;

        //I don't think this is possible, but I suupose you have to check
        if( strlen( $transient_name ) > 40 ) {

            return new \WP_Error( 'transient_name_error', __( 'transient_name is too long (' . strlen( $transient_name )
                . ' chars) and will not be findable in future', "usc-fb-events" ) );
        }

        return $transient_name;
    }

    /**
     * This one was fun.  We need a unique identifier for the calendars variable, but with a limit
     * on the length of the transient name, we couldn't just use the entire string or even trust that
     * the first (or last) six characters wouldn't just be one calendar.  So,
     *      sort the various calendars
     *      get the first letter from each calendar in sequence (until empty)
     *
     * this way, every calendar contributes to the whole. (max of 6 calendars)
     *
     * @since       0.9.8
     *
     * @param $calendars    a string of comma-separated calendars to call events from
     * @param $char_limit   the total length of the string.  returned string will be equal or less than this limit
     * @return string       part of our new transient name
     */
    private function generate_calendar_string_for_transient_cache( $calendars, $char_limit ) {

        if( empty( $calendars ) )
            return '';

        //so "usc,custom,western film" is now an array. ( csu, motsuc, mlifnretsew )
        $calendars_array =  explode( "," , strrev( str_replace( ' ', '', str_replace( '%20', ' ', $calendars ) ) ) );
        sort( $calendars_array );

        foreach ($calendars_array as $key => $calendar) {
            $calendars_array[$key] = str_split( $calendar );
        }

        $calendars_string = '';

        while( !empty( $calendars_array ) && $char_limit > 0 ) {

            //re-order the calendar
            $calendars_array = array_values($calendars_array);

            //basically, array_shift elements off the front of their
            //respective arrays while they're not empty and while max > 0;
            foreach( $calendars_array as $key => &$calendar_array ) {

                $calendars_string .= array_shift( $calendar_array );
                --$char_limit;

                if ( empty( $calendar_array ) )
                    unset($calendars_array[$key]);

            }
            unset($calendar_array);
        }

        return $calendars_string;
    }

    /**
     * Function checks for the existence of a specific cached object.
     *
     * @since    0.9.9
     *
     * @param $transient_name   looks for a cached object with this name
     * @return bool|mixed       returns 'false' if no object, or a json decoded array if found
     */
    public function if_stored_in_wordpress_transient_cache( $transient_name ) {

        $this->turn_off_object_cache_so_our_bloody_plugin_works();

        $events_or_false = get_site_transient( $transient_name );
        //if you get an empty result back, set this to false.

        $this->turn_object_caching_back_on_for_the_next_poor_sod();

        return ( false === $events_or_false || empty($events_or_false) ) ? false : json_decode( $events_or_false, true );
    }

    /**
     * Uses the WordPress HTTP API to call our AmAzE-O Facebook events api
     *
     * @since    0.9.8
     *
     * @param string $start     the start time (as a unix timestamp) when to start calling FB events from
     * @param string $end       the end time when to stop calling events
     * @param string $calendars the calendar names, separated by commas (which correspond to text files in a specific directory).
     * @param string $limit     the limit on events.  this is actually bogus right now, as the API doesn't support the option
     *
     * @return array            at this point, return open Facebook events as an indexed array
     */
    public function call_events_api( $start = '', $end = '', $calendars = '', $limit = '' ) {

        //for example
        //http://testwestern.com/api/events/events/usc?start=1388534401&end=1392072393&calendars=custom,usc

        $api_url = 'http://testwestern.com/api/events/events/usc?';

        //there HAS to be a start and an end, so we're just going to give the class a start and end time.
        $api_url .= ( !empty( $start ) )                ? "start=" . number_format( $start , 0 , '', '' )
            : "start=" . number_format( $this->start , 0 , '', '' );
        $api_url .= ( !empty( $end ) )                  ? "&end=" . number_format( $end , 0 , '', '' )
            : "&end=" . number_format( $this->end , 0 , '', '' );
        $api_url .= ( !empty( $calendars ) )            ? "&calendars=" . $calendars    : '';
        $api_url .= ( !empty( $limit ) && $limit > 0)   ? "&limit=" . $limit            : '';

        //$api_url = "http://testwestern.com/api/events/events/2014-04-01";

        $returned_string = wp_remote_retrieve_body( wp_remote_get( $this->add_http_if_not_exists($api_url) ) );

        if( empty( $returned_string ) ) {

            return new \WP_Error( 'api_error', __( 'Spot of trouble connecting to the events API', "usc-fb-events" ) );
        }

        /*echo '<pre>';
        var_dump( json_decode( $returned_string, true ) );
        echo '</pre>';*/

        return json_decode( $returned_string, true );
    }

    /**
     * Simple utility function. Add an "http://" to URLs without it.
     * Recognizes ftp://, ftps://, http:// and https:// in a case insensitive way.
     *
     * http://stackoverflow.com/questions/2762061/how-to-add-http-if-its-not-exists-in-the-url
     * @author Alix Axel
     *
     * @since    0.9.7
     *
     * @param $url      a url with or without an http:// prefix
     * @return string   a url with the http:// prefix, or whatever it had originally
     */
    private function add_http_if_not_exists($url) {

        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "http://" . $url;
        }
        return $url;
    }

}