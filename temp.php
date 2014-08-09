<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 09/08/14
 * Time: 12:39 AM
 */
$calendars = "uc, western%20film";

$calendars_array =  explode( "," , strrev( str_replace( ' ', '', str_replace( '%20', ' ', $calendars ) ) ) );

foreach ($calendars_array as $key => $calendar) {
    $calendars_array[$key] = str_split( $calendar );
}

$max = 6;
$calendars_string = '';

while( !empty( $calendars_array ) && $max > 0 ) {

    echo "<pre>";
    var_dump($calendars_array);
    echo "</pre><br>";

    $calendars_array = array_values($calendars_array);

    foreach( $calendars_array as $key => &$calendar_array ) {

        $calendars_string .= array_shift( $calendar_array );
        --$max;

        if ( empty( $calendar_array ) )
            unset($calendars_array[$key]);

    }
    unset($calendar_array);
}

var_dump($calendars_string);