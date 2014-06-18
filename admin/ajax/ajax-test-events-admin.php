<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 10/06/14
 * Time: 6:58 AM
 */

add_action("wp_ajax_return_to_or_remove_from_calendar", "return_to_or_remove_from_calendar");
add_action("wp_ajax_nopriv_return_to_or_remove_from_calendar", "login_please");

/**
 * This function right here is executed when, in the "manage events" menu, someone
 * clicks the "Remove From/Return To Calendar" buttons.
 * The event is then either removed from or returned to 'test_fbevents'
 * Based on tutorial here:
 * http://wp.smashingmagazine.com/2011/10/18/how-to-use-ajax-in-wordpress/
 *
 * @return 		returns an encoded JSON string
 */
function return_to_or_remove_from_calendar() {

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

    //@TODO: this should probably be re-directed
    if($button_id === 'remove_event_button')
        $response = remove_from_calendar_query($wpdb, $eid);
    if($button_id === 'display_event_button')
        $response = return_to_calendar_query($wpdb, $eid);

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
function remove_from_calendar_query($wpdb, $eid) {

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
function return_to_calendar_query($wpdb, $eid) {

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
function login_please() {
    echo "Log in before you do that, you cheeky monkey.";
    die();
}
