<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package   Plugin_Name
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 Your Name or Company Name
 */

    function echo_test_page_title( $page_title ) {
        echo '<h2>' . esc_html( $page_title ) . '</h2>';
    }

    function echo_test_section_title( $section_title ) {
        echo '<h3 class="title">' . esc_html( $section_title ) . '</h3>';
    }

    function echo_test_paragraph( $paragraph ) {
        echo '<p>' . esc_html( $paragraph ) . '</p>';
    }
?>




<div class="wrap">

	<?php
        echo_test_page_title( get_admin_page_title() );

        echo_test_paragraph( "List all Facebook events since April in a table, please." );

        echo_test_section_title( "Table of Facebook Events" );

        $events_array = Test_Events::call_api();

        if( is_array( $events_array ) ) {

            $total = $events_array['total'];
            $events = $events_array['events'];

            while( $total > 0 ) {

                $id = $total--;

                echo_test_paragraph(
                  "$id. " . $events[$total]['name']
                );

            }
        }


    ?>


	<!-- Provide markup for your options page here. -->

</div>
