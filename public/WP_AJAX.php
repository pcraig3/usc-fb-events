<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 18/06/14
 * Time: 11:05 AM
 */

class WP_AJAX {

    /**
     * Instance of this class.
     *
     * @since    0.9.2
     *
     * @var      object
     */
    protected static $instance = null;

    private function __construct() {

        add_action("wp_ajax_return_to_or_remove_from_calendar", array( $this, "return_to_or_remove_from_calendar" ) );
        add_action("wp_ajax_nopriv_return_to_or_remove_from_calendar", array( $this, "login_please") );

        add_action("wp_ajax_get_events", array( $this, "get_events" ) );
        add_action("wp_ajax_nopriv_get_events", array( $this, "get_events") );
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
     * Adds the WordPress Ajax Library to the frontend.
     * http://code.tutsplus.com/tutorials/a-primer-on-ajax-in-the-wordpress-frontend-actually-doing-it--wp-27073
     *
     * Kind of hack-y method, it looks like to me, but -- hey -- found it online.
     *
    public function add_ajax_library() {

        $html = '<script type="text/javascript">';
        $html .= 'var ajaxurl = "' . admin_url( 'admin-ajax.php' ) . '"';
        $html .= '</script>';

        echo $html;

    } // end add_ajax_library

    /**
     * This function right here is executed when, in the "manage events" menu, someone
     * clicks the "Remove From/Return To Calendar" buttons.
     * The event is then either removed from or returned to 'test_fbevents'
     * Based on tutorial here:
     * http://wp.smashingmagazine.com/2011/10/18/how-to-use-ajax-in-wordpress/
     *
     * @since   0.4.0
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
     * Does (a bit more than) what it says on the box.  gets all facebook and db events (and then merges them)
     *
     * @since    0.5.0
     */
    public function get_events() {

        $attr_id = $_POST['attr_id'];

        if ( !wp_verify_nonce( $_POST['nonce'], $attr_id . "_nonce")) {
            exit("No naughty business please");
        }

        $whitelist = ( isset($_POST['whitelist']) ) ? $_POST['whitelist'] : false;
        $remove_events = ( isset($_POST['remove_events']) ) ? $_POST['remove_events'] : false;

        $te = Test_Events::get_instance();

        $response = $te->call_api();

        $response = $te->facebook_urls($response);

        if($whitelist)
            $response['events'] = DB_API::whitelist_array_items($response['events']);

        $response = $te->merge_fb_and_db_events($response);

        if($remove_events)
            $response = $te->remove_removed_events($response);

        //javascript CANNOT understand dates
        $response = $te->date_strings_to_timestamps($response);

        $result['response'] = $response;
        $result['success'] = ( $response === false ) ? false : true;

        echo json_encode($result);
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


}