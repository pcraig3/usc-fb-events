<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 11/06/14
 * Time: 9:44 PM
 */

$html_string = '<blockquote id="events">';

//if there is a 'total'
if( ! isset($events_array['total']) ) {
    return;
}

$total = intval( $events_array['total'] );

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
$events_array = $events_array['events'];

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