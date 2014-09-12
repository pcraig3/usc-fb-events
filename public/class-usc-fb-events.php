<?php
/**
 * USC FB Events.
 *
 * @package   USC_FB_Events
 * @author    Paul Craig <pcraig3@uwo.ca>
 * @license   GPL-2.0+
 * @copyright 2014
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * @package USC_FB_Events
 * @author  Paul Craig <pcraig3@uwo.ca>
 */
class USC_FB_Events {

    /**
     * Plugin version, used for cache-busting of style and script file references.
     *
     * @since   1.0.0
     *
     * @var     string
     */
    const VERSION = '1.0.0';

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
    protected $plugin_slug = 'usc-fb-events';

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
     * @since     1.0.0
     */
    private function __construct() {

        $this->wp_ajax = \USC_FB_Events\WP_AJAX::get_instance();

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

        add_shortcode( 'usc_fb_events', array( $this, 'return_usc_fb_events') );

        add_action( 'init', '\USC_FB_Events\DB_API::register_fb_events_table', '1' );
        add_action( 'switch_blog', '\USC_FB_Events\DB_API::register_fb_events_table' );

        /*add_action( 'widgets_init', array( $this, 'usc_fb_events_register_sidebars' ) ); */

        add_filter( 'eventorganiser_inject_my_events', array( $this, 'event_organiser_add_fb_events_to_fullcalendar'), 10, 2);

        add_action( 'wp_enqueue_scripts', array( $this, 'event_organiser_mobile_view_for_fullcalender' ) );

    }

    /**
     *
     * @since     1.0.0
     */
    public function event_organiser_add_fb_events_to_fullcalendar( array $eventsarray, $query ) {

        /*
         * Get the blog timezone using one of Stephen Harris' event-organiser functions
         * see "includes/event-organiser-utility-functions.php"
         */
        $tz = $this->wp_ajax->get_event_organiser_timezone();

        /*
         * Pretty basic.  Get start date and end date as passed in through the query
         */
        $start = $query['event_end_after'];  //start time
        $end = $query['event_start_before'];  //end time

        /*
         * Set the categories in the category array, if this calendar is restricted by category
         *
         * NOTE: because EO is using taxonomy slugs to identify categories, we are going to get
         * category requests like 'usc-2' or 'clubs-2' when we really want 'usc' or 'clubs'
         *
         * To get around this, I've cross referenced the slugs with the WP event-categories taxonomy
         * and then returned the name of any matching texonomies.
         *
         * Convert any dashes in the slug into '%20s' for the call
         */
        $args = array(
            'hide_empty'        => false,
        );

        //get the wordpress terms for the event-category taxonomy
        $wp_event_categories = get_terms( 'event-category', $args );

        $category_array = array();

        //probably overkill, but preferable to underkill
        if( isset( $query['tax_query'] ) && !empty( $query['tax_query'] )) {

            foreach( $query['tax_query'] as $tax_query ) {

                if( $tax_query['taxonomy'] === 'event-category' ) {

                    //if we've gotten this far, we have at least one category slug
                    foreach( $tax_query['terms'] as $term )
                        //a term night be "usc-2"
                        //now check for the slug in the event-categories taxonomy array of objects
                        foreach( $wp_event_categories as $wp_event_category )
                            if( $term === $wp_event_category->slug )
                                array_push( $category_array, strtolower( $wp_event_category->name ) );
                }
            }
        }
        //else, if no tax_query is set, just build a string with all term names
        else {
            foreach( $wp_event_categories as $wp_event_category )
                array_push( $category_array, strtolower( $wp_event_category->name ) );
        }

        $calendar_string = '';

        if( ! empty( $category_array ) )
            $calendar_string = str_replace( ' ', '%20', implode( ',', $category_array ) );

        /*
         * Generate the transient value, if there were one
         */
        $transient_name = $this->wp_ajax->generate_transient_name(
            $this->wp_ajax->start_end_dates_to_timestamps($start),  //start and end are 'YYYY-mm-dd' strings and we need timestamps
            $this->wp_ajax->start_end_dates_to_timestamps($end),
            $calendar_string,
            0 );

        /*
         * Call the events from Facebook
         */
        $response = $this->wp_ajax->get_events_whether_cached_or_not(
            $this->wp_ajax->start_end_dates_to_timestamps($start),  //start and end are 'YYYY-mm-dd' strings and we need timestamps
            $this->wp_ajax->start_end_dates_to_timestamps($end),
            $calendar_string,
            0,
            $transient_name
        );

        if( empty( $response['events'] ) )
            return $eventsarray;

        /*
         * This is a bit strange, admittedly, but we want the offset in th facebook start_time before the event is modified
         */
        $fb_event_offsets = array();

        foreach( $response['events'] as $event ) {
            //get the offset
            //reverse the string, find the first hyphen and then
            $last_hyphen = strpos(strrev($event['start_time']), '-');

            $fb_event_offsets[$event['eid']] = substr($event['start_time'], -($last_hyphen + 1));
        }

        /*
         * Update FB events with our database modifications or removals (if any)
         */
        $response = $this->wp_ajax->merge_fb_and_db_events($response);
        $response = $this->wp_ajax->remove_removed_events($response);

        $events = $response['events'];

        /*
         * This is where we set up individual events
         */
        foreach($events as &$event) {

            /*
             * Getting the original start and end dates allows us to figure out the time interval between them
             * This is useful if we want to modify the start_time using our plugin
             */
            $fb_original_start = new DateTime($event['eventStartDate']);
            $fb_original_end = new DateTime($event['eventEndDate']);
            $fb_original_timediff = $fb_original_start->diff($fb_original_end, true);
            $fb_original_offset = ( isset($fb_event_offsets[$event['eid']]) ) ? $fb_event_offsets[$event['eid']] : '' ;

            $fb_start = new DateTime($this->reformat_start_time_like_facebook($event['start_time'], $fb_original_offset),
                $tz);
            $fb_end =  new DateTime($this->reformat_start_time_like_facebook($event['start_time'], $fb_original_offset),
                $tz);
            $fb_end->add($fb_original_timediff);

            //$fb_end->add(new DateInterval('PT2H'));

            /*
             * format the date like Stephen Harris does it in includes/event-organiser-ajax.php
             */

            //if there is a preferred time format, use that.  Otherwise, use the default format ("7:30 pm").
            $time_format = ( get_option('time_format') ) ? get_option('time_format') : 'g:i a';

            if( $fb_start->format('Y-m-d') != $fb_end->format('Y-m-d') ){
                //Start & ends on different days

                //if( !eo_is_all_day() ){  //forget about all_day events
                //Not all day, include time
                $date = eo_format_datetime($fb_start,'F j '.$time_format).' - '.eo_format_datetime($fb_end,'F j '.$time_format);

            }else{
                //Start and end on the same day

                //if( !eo_is_all_day() ){  forget about all_day events
                //Not all day, include time
                $date = eo_format_datetime($fb_start,$format="F j, Y $time_format").' - '.eo_format_datetime($fb_end,$time_format);

            }

            //set a host for the event description
            $host = ( !empty($event['host']) ) ? '<strong>' . esc_html($event['host']) . '</strong>' : '' ;

            //set a location for the event description
            $location = ( !empty($event['location']) ) ? '@ ' . esc_html($event['location']) : '' ;

            $tickets = ( !empty($event['ticket_uri']) ) ? '</br></br><a href="' . esc_url( $event['ticket_uri']) . '" target="_blank">Get Tickets!</a>' : '' ;


            //set a description or an error message
            $fb_event_description = ( !empty( $event['description'] ) ) ? $event['description']
                : 'This event has not provided a description.  Maybe you can message the host directly on '
                .' <a href="http://facebook.com">Facebook.</a>';

            if( strlen( $fb_event_description ) > 200 )
                $fb_event_description = substr($fb_event_description, 0, 200) . "...";


            /*
             * Start to set the classnames
             */
            $classNames = array('eo-event', 'eo-fb-event');

            /*
             * set the calendar as a classname
             */
            array_push($classNames, 'eo-fb-event-' . str_replace(' ', '-', $event['calendar']));

            /*
             * Set a class on the event to indicate whether or not it has passed. Logic is identical to Stephen Harris'
             */
            $now = new DateTime(null,$tz);
            if($fb_start <= $now)
                array_push($classNames, 'eo-past-event');
            else
                array_push($classNames, 'eo-future-event');

            /*
             * Set a default bg color for the event
             * If this event has a category color (very likely), then this value will be overwritten
             */
            $color = '#16811B';

            /*
             * Set the event categories as their calendars
             * We don't have to worry about checking these with the category_array near the beginning of this method
             * because events whose categories are not allowed simply won't be returned from Facebook
             */
            $fb_event_categories = array();

            if( !empty( $event['calendar'] ) )
                array_push( $fb_event_categories, $event['calendar'] );

            $fb_event_categories_slugs = array();

            //$wp_event_categories was set near the beginning of the method
            if( !empty( $fb_event_categories ) && !empty( $wp_event_categories ) ) {
                foreach( $fb_event_categories as $fb_event_category ) {
                    foreach( $wp_event_categories as $wp_event_category ) {

                        //if the fb_event_category matches the lowercased wp_category->name
                        if( trim( $fb_event_category ) === trim( strtolower( $wp_event_category->name ) ) ) {
                            array_push( $fb_event_categories_slugs, $wp_event_category->slug );

                            /* Logic is the same as Stephen Harris'.  see "includes/event-organiser-event-functions.php" */
                            if( ! empty( $wp_event_category->color ) ) {
                                $color_code = ltrim( $wp_event_category->color, '#' );
                                if ( ctype_xdigit( $color_code ) && (strlen( $color_code ) == 6 || strlen( $color_code ) == 3) ) {
                                    $color = '#'.$color_code;
                                }
                            }
                        }
                    }
                }
            }

            //Just going through the motions.
            //There's currently no way for a FB event to have more than one calendar/category
            if( !empty( $fb_event_categories_slugs ) )
                foreach( $fb_event_categories_slugs as $fb_event_category_slug )
                    array_push( $classNames, 'category-' . $fb_event_category_slug );


            /*
            array_push($classNames, 'fb-month-start-' . $start);
            array_push($classNames, 'fb-month-end-' . $end);
            array_push($classNames, 'fb-calendars-' . $calendar_string);

            array_push($classNames, 'fb-transient-' . $transient_name);
            */
            $if_cached = ( $response['events_stored_in_cache'] ) ? '' : 'not-' ;
            array_push($classNames, 'fb-' . $if_cached . 'cached');


            //okay, so now it's time to actually create the event
            $fb_event = array(

                'className' => $classNames,
                // 'venue-' . strtolower( esc_html( $event['location'] ) ) ),  we're not using this right now either
                'title' 	=> $this->decodeHtmlEnt( esc_html( $event['title'] ) ),
                'host'      => $this->decodeHtmlEnt( esc_html( $event['host'] ) ),
                'url'		=> esc_url($event['url']),
                'allDay'	=> false,
                'start'		=> $fb_start->format('Y-m-d\TH:i:s\Z'),
                'end'		=> $fb_end->format('Y-m-d\TH:i:s\Z'),

                'description' => $date . '</br></br>' . $host . '</br></br>' . $location . $tickets
                    . '</br></br>' . $fb_event_description,
                //'venue'		=> $event['venue']['id'],  this is basically useless
                //className = 'venue-university-community-center'

                'category'	=> $fb_event_categories_slugs,


                'tags'		=> array(),
                //className = 'tag-tagSlug'

                'color'     => $color,
                'textColor'	=> '#ffffff',

                //extra
                'eid'       => $event['eid'],
            );

            array_push($eventsarray, $fb_event);
        }

        //return $query;

        return $eventsarray;
    }



    /**
     * Function to decode HTML entities that look like &#2423;
     * Ripped off the PHP reference page from one of the comments.
     * http://php.net/manual/en/function.html-entity-decode.php#111859
     *
     * @author Benjamin
     * most likely a badass mofo
     *
     * @param $str      string with horrible HTML codes to decode
     *
     * @since     1.0.0
     *
     * @return string   returns your string with the html codes swapped for sane characters
     */
    private function decodeHtmlEnt($str) {
        $ret = html_entity_decode($str, ENT_COMPAT, 'UTF-8');
        $p2 = -1;
        for(;;) {
            $p = strpos($ret, '&#', $p2+1);
            if ($p === FALSE)
                break;
            $p2 = strpos($ret, ';', $p);
            if ($p2 === FALSE)
                break;

            if (substr($ret, $p+2, 1) == 'x')
                $char = hexdec(substr($ret, $p+3, $p2-$p-3));
            else
                $char = intval(substr($ret, $p+2, $p2-$p-2));

            //echo "$char\n";
            $newchar = iconv(
                'UCS-4', 'UTF-8',
                chr(($char>>24)&0xFF).chr(($char>>16)&0xFF).chr(($char>>8)&0xFF).chr($char&0xFF)
            );
            //echo "$newchar<$p<$p2<<\n";
            $ret = substr_replace($ret, $newchar, $p, 1+$p2-$p);
            $p2 = $p + strlen($newchar);
        }
        return $ret;
    }

    /**
     * If the events have been updated from the database, they've lost the offset value -- which, it
     * turns out -- is actually pretty useful.   This short little method adds an offset back into
     * the start_time string and rams a 'T' into the middle of it, mimicking the Facebook format.
     *
     * @since     1.0.0
     *
     * @param $start_time
     * @param $offset
     * @return string
     */
    private function reformat_start_time_like_facebook( $start_time, $offset = '-4000' ) {

        if (strpos($start_time,'T') !== false)
            return $start_time;

        $offset = ( empty( $offset ) ) ? '-4000' : $offset;

        //else, collapse whitespace and slap a "-0400" on the end (2014-08-30T22:00:00-0400)
        return str_replace(' ', 'T', $start_time) . $offset;
    }

    /**
     * Proof-of-concept method that we don't use anymore.
     * Adds a nonsense event to the event calendar.
     *
     * @since     1.0.0
     *
     * @param array $eventsarray
     * @param $query
     * @return array
     */
    public function add_a_fake_event_to_the_event_organiser( array $eventsarray, $query ) {

        $tz = $this->wp_ajax->get_event_organiser_timezone();

        $start = $query['event_end_after'];  //start time
        $end = $query['event_start_before'];  //end time

        $fake_start = new DateTime("now", $tz);
        $fake_end = new DateTime("now", $tz);
        $fake_end->add(new DateInterval('PT2H'));

        $fake_event = array(

            'className' => array('eo-event', 'eo-past-event', $start, $end),
            'title' 	=> 'Fake Event',
            'url'		=> 'http://google.com',
            'allDay'	=> false,
            'start'		=> $fake_start->format('Y-m-d\TH:i:s\Z'),
            'end'		=> $fake_end->format('Y-m-d\TH:i:s\Z'),
            'description' => $fake_start->format('F j, Y H:i') . ' - ' . $fake_end->format('H:i')
                . '</br></br>' . 'This event is fake, but hopefully the JS file doesn\'t know.',
            //'venue'		=> 560
            //className = 'venue-university-community-center'
            'category'	=> array(),
            //className = 'category-categorySlug'
            'tags'		=> array(),
            //className = 'tag-tagSlug'
            'color'     => '#16811B',
            'textColor'	=> '#ffffff',


            //_end = Date 2014-08-23T03:55:00.000Z
            //_id = "_fc1"
            //_start = Date 2014-08-22T23:30:00.000Z
            //allDay = false
            //category = array();
            //classname = array( 'eo-event', 'eo-past-event', 'venue-univeristy-community-centre');
            //description = 'August 22, 2014 7:30 pm - 11:55 pm</br></br>Western Film is gonna re-open and it's gonna be sweet. Can't even wait for Captain America 2.'
            //end = Date 2014-08-23T03:55:00.000Z
            //source = Object (?)
            //start = Date 2014-08-22T23:30:00.000Z
            //tags = array();
            //textColor = "#ffffff";
            //title = "Western Film Redux"
            //url = "http://westernusc.org/events/event/western-film-redux/"
            //venue = 560

        );

        array_push($eventsarray, $fake_event);

        return $eventsarray;
    }

    /**
     * @TODO: Work it good.
     *
     * @since     1.0.0
     */
    public function event_organiser_mobile_view_for_fullcalender() {

        global $post;

        if( has_shortcode( $post->post_content, 'eo_fullcalendar' ) ) {

            /* put this in a separate method */
            /* what we want to do is include the filter-init again because it knows about the caching method */
            /*
             * options.ajax_url         = we already have this
             * options.transient_name   = we can generate if we have the right stuff
             * options.start            = we can get this from the JS file.
             * options.end              = we can get this in the JS
             * options.calendars        = we can get this in the JS
             * options.limit            = no limit
             */
            wp_enqueue_script( $this->plugin_slug . '-event-organiser', plugins_url( 'assets/js/event-organiser.js', __FILE__ ), array( 'jquery' ), self::VERSION );

            wp_enqueue_script( 'tinysort', plugins_url( '../admin/assets/js/jquery.tinysort.min.js', __FILE__ ), array( 'jquery' ), self::VERSION );
            wp_enqueue_script( 'filterjs', plugins_url( '../admin/assets/js/filter.js', __FILE__ ), array( 'jquery', 'tinysort', 'jquery-ui-core' ), self::VERSION );
            wp_enqueue_script( 'init_filterjs', plugins_url( '/assets/js/init-filter.js', __FILE__ ), array( 'jquery', 'tinysort', 'jquery-ui-core', 'filterjs' ), self::VERSION );

            wp_enqueue_script( $this->plugin_slug . '-classList', plugins_url( 'assets/js/util/classList.js', __FILE__ ), array(), self::VERSION, true );
            wp_enqueue_script( $this->plugin_slug . '-indexOf', plugins_url( 'assets/js/util/indexOf.js', __FILE__ ), array(), self::VERSION, true );
            wp_enqueue_script( $this->plugin_slug . '-event-organiser-fullcalendar-mobile', plugins_url( 'assets/js/event-organiser-fullcalendar-mobile.js', __FILE__ ),
                array( 'jquery', 'eo_front', 'init_filterjs', $this->plugin_slug . '-classList', $this->plugin_slug . '-indexOf' ), self::VERSION, true );

            $id = 'fullcalendar__list';
            $plugin_prefix = 'usc_fb_events_';
            //declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
            wp_localize_script( $this->plugin_slug . '-event-organiser-fullcalendar-mobile', "options", array(
                //'is_cached' => $is_cached,
                'ajax_url'      => admin_url( 'admin-ajax.php' ),
                'id'            => $id,
                'plugin_prefix' => $plugin_prefix,
                'nonce'         => wp_create_nonce( $id . '_nonce' ),
                //'transient_name' => $this->wp_db->transient_name,
            ) );

            //last but not least: CSS file
            wp_enqueue_style( $this->plugin_slug . '-public-fullcalendar', plugins_url( 'assets/css/public-fullcalendar.css', __FILE__ ), array(), self::VERSION );

        }
    }

    /**
     * Function meant to target the [usc_fb_events] shortcode.  Grabs the attributes in the shortcode to
     * call a function somewhere down there.
     *
     * @param $atts         create an associative array based on attributes and values in the shortcode
     *
     * @since     1.0.0
     *
     * @return string       a complimentary adjective for students
     */
    public function return_usc_fb_events ( $atts ) {

        //initialize your variables
        $get = $show = $start = $end = $calendars = $limit = $title = $result = false;

        $april_2014 = 1396310401;
        //set a default datetime
        $this->wp_ajax->set_server_to_local_time();
        $today= new DateTime('now');


        extract(
            shortcode_atts(
                array(
                    'get'       => 'events',
                    'show'      => 'count',
                    'start'     => $today->getTimestamp(),
                    'end'       => $today->getTimestamp() + YEAR_IN_SECONDS,
                    'calendars' => '',
                    'limit'     => 0,
                    'title'     => 'Events',
                ), $atts ),
            EXTR_OVERWRITE);

        //P2Y4DT6H8M  for example
        /*
         * DateInterval format: http://php.net/manual/en/dateinterval.construct.php
         * 'P2Y4DT6H8M', for example, means '2 years, 4 days, 6 hours, 8 minutes.'  P == 'Period' and T == 'Time'
         */
        if( substr($start, 0, 1) === 'P' || substr($start, 0, 2) === '-P' )  ///this means a date interval
            $start = $this->wp_ajax->start_end_dates_to_timestamps('now', $start);

        //I get that copying + pasting is bad, but it didn't seem worth doing anything more confusing just for two values.
        if( substr($end, 0, 1) === 'P' || substr($end, 0, 2) === '-P' )  ///this means a date interval
            $end = $this->wp_ajax->start_end_dates_to_timestamps('now', $end);

            //okay, now change the start and end dates into the timestamp for just the current DAY so that our caching system takes effect.
        if( !empty($start) )
            $start = strtotime( date('Y-m-d', $start) );

        if( !empty($end) )
            $end = strtotime( date('Y-m-d', $end) );

        $this->wp_ajax->set_server_back_to_default_time();

        $parameters = array(

            $start,
            $end,
            $calendars,
            intval($limit),
            $title,
        );

        $usc_fb_events_function = (string) $get . "_" . (string) $show;

        ob_start();

        /* @TODO: Explain yourself. */
        echo call_user_func_array( array( $this, $usc_fb_events_function ), $parameters );

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

        //@TODO:caching
        $events_array = $this->wp_ajax->call_events_api();

        $events_array = $this->wp_ajax->merge_fb_and_db_events($events_array);

        $events_array = $this->wp_ajax->remove_removed_events($events_array);
        $events_array = $this->wp_ajax->date_strings_to_timestamps($events_array);

        return require_once('views/in-page-list.php');
    }

    /**
     * Build the Event list brought in by Ajax with filter.js applied to them
     * Queues up the relevant .js files to get it going.
     *
     * @since    0.9.8
     */
    private function events_ajax( $start, $end, $calendars, $limit ) {


        wp_enqueue_script( 'tinysort', plugins_url( '../admin/assets/js/jquery.tinysort.min.js', __FILE__ ), array( 'jquery' ), self::VERSION );
        wp_enqueue_script( 'filterjs', plugins_url( '../admin/assets/js/filter.js', __FILE__ ), array( 'jquery', 'tinysort', 'jquery-ui-core' ), self::VERSION );
        wp_enqueue_script( 'init_filterjs', plugins_url( '/assets/js/init-filter.js', __FILE__ ), array( 'jquery', 'tinysort', 'jquery-ui-core', 'filterjs' ), self::VERSION );
        wp_enqueue_script( 'public_filterjs', plugins_url( '/assets/js/public-filter.js', __FILE__ ), array( 'jquery', 'tinysort', 'jquery-ui-core', 'filterjs', 'init_filterjs' ), self::VERSION );

        // declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
        wp_localize_script( 'public_filterjs', "options", array(
                'ajax_url'          => admin_url( 'admin-ajax.php' ),
                'start'             => $start,
                'end'               => $end,
                'calendars'         => $calendars,
                'limit'             => $limit,
            )
        );

        return require_once('views/ajax-list.php');
    }

    /**
     * Build the Event list brought in by Ajax with filter.js applied to them
     * Queues up the relevant .js files to get it going.
     *
     * @since    0.9.8
     */
    private function events_widget( $start, $end, $calendars, $limit, $title ) {

        wp_enqueue_script( 'tinysort', plugins_url( '../admin/assets/js/jquery.tinysort.min.js', __FILE__ ), array( 'jquery' ), self::VERSION );
        wp_enqueue_script( 'filterjs', plugins_url( '../admin/assets/js/filter.js', __FILE__ ), array( 'jquery', 'tinysort', 'jquery-ui-core' ), self::VERSION );
        wp_enqueue_script( 'init_filterjs', plugins_url( '/assets/js/init-filter.js', __FILE__ ), array( 'jquery', 'tinysort', 'jquery-ui-core', 'filterjs' ), self::VERSION );
        wp_enqueue_script( 'public_widgetjs', plugins_url( '/assets/js/public-widget.js', __FILE__ ), array( 'jquery', 'tinysort', 'jquery-ui-core', 'filterjs', 'init_filterjs' ), self::VERSION );


        // declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
        wp_localize_script( 'public_widgetjs', "options", array(
                'ajax_url'          => admin_url( 'admin-ajax.php' ),
                'start'             => $start,
                'end'               => $end,
                'calendars'         => $calendars,
                'limit'             => $limit,
            )
        );

            //enqueue the css file for the homepage widget
        wp_enqueue_style( 'public_widgetcss', plugins_url( 'assets/css/public-widget.css', __FILE__ ), array(), self::VERSION );

        return require_once('views/widget-list.php');
    }

    /**
     * Guess what this one does.
     *
     * @since    1.0.0.
     *
    public function usc_fb_events_register_sidebars() {

        /* Register the usc events page sidebar. *
        register_sidebar(
            array(
                'id' => 'usc_fb_events_widget',
                'name' => __( 'USC FB Events Sidebar', 'usc-fb-events' ),
                'description' => __( 'Widget meant only for the Event Calendar Page.', 'usc-fb-events' ),
                'before_widget' => '<aside id="%1$s" class="et_pb_widget %2$s">',
                'after_widget' => '</aside>',
                'before_title' => '<h4 class="widgettitle">',
                'after_title' => '</h4>'
            )
        );
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

        \USC_FB_Events\DB_API::create_fb_events_table();
    }

    /**
     * Fired for each blog when the plugin is deactivated.
     *
     * @since    0.2.3
     */
    private static function single_deactivate() {
        // Define deactivation functionality here

        \USC_FB_Events\DB_API::drop_fb_events_table();
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
