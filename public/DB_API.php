<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 11/06/14
 * Time: 10:23 PM
 */

namespace USC_FB_Events;

class DB_API {

    /**
     * Store our table name in $wpdb with correct prefix
     * Prefix will vary between sites so hook onto switch_blog too
     *
     * @since   0.4.0 (Stolen from Stephan Harris' excellent examples)
     * https://github.com/stephenh1988/wptuts-user-activity-log/blob/master/wptuts-user-log.php
     */
    public static function register_fb_events_table() {
        global $wpdb;
        $wpdb->fb_events = $wpdb->prefix . 'usc_fb_events';
    }

    /**
     * Creates our table
     * Hooked onto our single_activate function
     * @since 0.2.3
     */
    public static function create_fb_events_table(){

        global $wpdb;
        global $charset_collate;

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        //Call this manually as we may have missed the init hook
        DB_API::register_fb_events_table();

        $sql_create_table = "CREATE TABLE {$wpdb->fb_events} (
        eid bigint(20) NOT NULL,
        name varchar(255) NULL,
        start_time datetime NULL,
        host varchar(127) NULL,
        location varchar(255) NULL,
        ticket_uri varchar(511) NULL,
        url varchar(511) NULL,
        modified bool NOT NULL default '0',
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
    public static function drop_fb_events_table(){

        global $wpdb;

        $wpdb->fb_events = "usc_fb_events";

        $sql = "DROP TABLE IF EXISTS $wpdb->fb_events;";
        $wpdb->query($sql);
    }

    /**
     *
     * @since   0.4.0 (Stolen from Stephan Harris' excellent examples)
     * https://github.com/stephenh1988/wptuts-user-activity-log/blob/master/wptuts-user-log.php
     *
     * @return array and array of columns in our new DB table.  This way we can check queries against them
     */
    private static function get_fb_events_table_columns(){
        return array(
            'eid'=> '%s',
            'name'=> '%s',
            'start_time'=>'%s',
            'host'=>'%s',
            'location'=>'%s',
            'ticket_uri'=>'%s',
            'url'=>'%s',
            'modified'=>'%d',
            'removed'=>'%d',
        );
    }

    public static function whitelist_array_items( array $array ) {

        //Initialise column format array
        $column_formats = DB_API::get_fb_events_table_columns();

        foreach($array as &$item) {

            //Force fields to lower case
            $item = array_change_key_case ( $item, CASE_LOWER );

            //White list columns
            $item = array_intersect_key( $item, $column_formats );
        }

        return $array;
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
            'removed'=> '0',
        ));

        if( ! is_numeric( $data['eid'] ) )
            return false;

        //Check date validity
        if( isset($data['start_time'] ) && $data['start_time'] !== "0000-00-00 00:00:00" ) {
            //Convert activity date from local timestamp to GMT mysql format
            $data['start_time'] = date_i18n( 'Y-m-d H:i:s', strtotime( $data['start_time'] ), true );
        }

        //WHATEVER
        if( isset($data['ticket_uri']) ) {
            $data['ticket_uri'] = esc_url_raw( $data['ticket_uri'] );
        }

        if( isset($data['url']) ) {
            $data['url'] = esc_url_raw( $data['url'] );
        }

        //Initialise column format array
        $column_formats = DB_API::get_fb_events_table_columns();

        //Force fields to lower case
        $data = array_change_key_case ( $data, CASE_LOWER );

        //White list columns
        $data = array_intersect_key( $data, $column_formats );

        //Reorder $column_formats to match the order of columns given in $data
        $data_keys = array_keys($data);
        $column_formats = array_merge(array_flip($data_keys), $column_formats);

        return $wpdb->insert($wpdb->fb_events, $data, $column_formats);

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
        if( isset($data['start_time'] ) && $data['start_time'] !== "0000-00-00 00:00:00" ) {
            //Convert activity date from local timestamp to GMT mysql format
            $data['start_time'] = date_i18n( 'Y-m-d H:i:s', strtotime( $data['start_time'] ), true );
        }


        //WHATEVER
        if( isset($data['ticket_uri']) ) {
            $data['ticket_uri'] = esc_url_raw( $data['ticket_uri'] );
        }

        if( isset($data['url']) ) {
            $data['url'] = esc_url_raw( $data['url'] );
        }

            //Initialise column format array
        $column_formats = DB_API::get_fb_events_table_columns();

        //Force fields to lower case
        $data = array_change_key_case ( $data, CASE_LOWER );

        //White list columns
        $data = array_intersect_key($data, $column_formats);

        //Reorder $column_formats to match the order of columns given in $data
        $data_keys = array_keys($data);
        $column_formats = array_merge(array_flip($data_keys), $column_formats);

        if ( false === $wpdb->update($wpdb->fb_events, $data, array('eid'=>$eid), $column_formats) ) {
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
    public static function get_fb_events( $query=array() ){
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
        $cache_key = 'get_fb_events:'.md5( serialize($query));
        $cache = wp_cache_get( $cache_key );
        if ( false !== $cache ) {
            $cache = apply_filters('get_fb_events', $cache, $query);
            return $cache;
        }
        extract($query, EXTR_OVERWRITE);

        /* SQL Select */
        //Whitelist of allowed fields
        $allowed_fields = array_keys(DB_API::get_fb_events_table_columns());

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
            $select_sql = "SELECT* FROM {$wpdb->fb_events}";
        }elseif( 'count' == $fields ) {
            $select_sql = "SELECT COUNT(*) FROM {$wpdb->fb_events}";
        }else{
            $select_sql = "SELECT ".implode(',',$fields)." FROM {$wpdb->fb_events}";
        }

        /*SQL Join */
        //We don't need this, but we'll allow it be filtered (see 'fb_events_clauses' )
        $join_sql='';

        /* SQL Where */
        //Initialise WHERE
        $where_sql = 'WHERE 1=1';
        if( !empty($eid) )
            $where_sql .= $wpdb->prepare(' AND eid=%s', $eid);

        //because we still want to use zeros
        if( isset($removed) && is_numeric($removed) )
            $where_sql .= $wpdb->prepare(' AND removed=%d', $removed);

        //because we still want to use zeros
        if( isset($modified) && is_numeric($modified) )
            $where_sql .= $wpdb->prepare(' AND modified=%d', $modified);

        if( !empty($append_to_where) )
            $where_sql .= $append_to_where;

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
        $clauses = apply_filters( 'fb_events_clauses', compact( $pieces ), $query );
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
        $events = apply_filters('get_fb_events', $events, $query);

        return $events;
    }

    /**
     * Deletes fbevent from 'usc_fb_events'
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

        $sql = $wpdb->prepare("DELETE from {$wpdb->fb_events} WHERE eid = %s", $eid);

        if( !$wpdb->query( $sql ) )
            return false;

        do_action('deleted_fbevent', $eid);

        return true;
    }

     /**
      * Does what it says on the box.  gets removed events (and then applies 'removed' class to list items)
      *
      * @since   0.4.0
      *
      * @return array of objects if results. empty array if no results.
      */
     public static function get_removed_events_eids() {

         return DB_API::get_fb_events( array (
            'fields' =>     array( 'eid' ),
            'removed' =>    1
            )
         );

     }

    /**
     * USED BY THE ADMIN CLASS */

    public static function get_event_count_by_eid( $eid ) {

        return DB_API::get_fb_events( array(
                'fields' => 'count',
                'eid' =>    $eid
        ));
    }

    public static function get_unmodified_event_count_by_eid( $eid ) {

        return DB_API::get_fb_events( array(
            'fields' => 'count',
            'eid' =>    $eid,
            'append_to_where' =>  " AND (start_time IS NULL OR start_time = '0000-00-00 00:00:00')"
                                . " AND (location IS NULL OR location = '')"
                                . " AND (host IS NULL OR host = '')"
                                . " AND (ticket_uri IS NULL OR ticket_uri = '')"
                                . " AND (url IS NULL OR url = '')",
        ));
    }

    public static function insert_on_duplicate_key_update($eid, array $data = array() ) {

        if ( DB_API::get_event_count_by_eid( $eid ) ) {
            $updated = DB_API::update_fbevent( $eid, $data );

            if( DB_API::get_unmodified_event_count_by_eid( $eid ) )
                DB_API::delete_fb_event( $eid );

            return $updated;
        }

        $data['eid'] = $eid;
        return DB_API::insert_fbevent( $data );
    }

}