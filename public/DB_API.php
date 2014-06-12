<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 11/06/14
 * Time: 10:23 PM
 */


class DB_API {

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
    public static function create_fbevents_table(){

        global $wpdb;
        global $charset_collate;

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        //Call this manually as we may have missed the init hook
        DB_API::register_fbevents_table();

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
    public static function drop_fbevents_table(){

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
    private static function get_fbevents_table_columns(){
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
        $column_formats = DB_API::get_fbevents_table_columns();

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
        $column_formats = DB_API::get_fbevents_table_columns();

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
        $allowed_fields = array_keys(DB_API::get_fbevents_table_columns());

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

        if( !empty($append_to_where) )
            $where_sql .= $append_to_where;

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
      *
      * @return array of objects if results. empty array if no results.
      */
     public static function get_removed_events_eids() {

     return DB_API::get_fbevents( array (
        'fields' =>     array( 'eid' ),
        'removed' =>    1
        )
     );

     }

    /**
     * Grabs events who have not been removed from the calendar and whose values have been modified.
     *
     * @since   0.4.2
     *
     * @return array of objects if results. empty array if no results.
     */
    public static function get_modified_unremoved_events() {

     return DB_API::get_fbevents( array(
         //'fields' << not setting this means SELECT*
         'removed' =>           0,
         'append_to_where' =>   " AND start_time IS NOT NULL AND location IS NOT NULL AND host IS NOT NULL ",

     ));

    }

    /**
     * USED BY THE ADMIN CLASS */

    public static function get_event_count_by_eid( $eid ) {

        return DB_API::get_fbevents( array(
                'fields' => 'count',
                'eid' =>    $eid
        ));
    }

    public static function get_unmodified_event_count_by_eid( $eid ) {

        return DB_API::get_fbevents( array(
            'fields' => 'count',
            'eid' =>    $eid,
            'append_to_where' =>  " AND start_time IS NULL AND location IS NULL AND host IS NULL ",
        ));
    }

}