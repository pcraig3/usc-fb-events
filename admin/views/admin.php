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

    function echo_test_button( $button_text, $id, $tab_index ) {

        $button_text = esc_html( $button_text );
        $id = esc_attr( $id );
        $tab_index = ( is_numeric( $tab_index ) ) ? $tab_index : -1;

        submit_button( $button_text, 'large', $id, false,
            array(
                'tabindex' 		=> $tab_index,
                'data-nonce' 	=> wp_create_nonce($id . "_nonce"),
                'class'			 => 'button',
            ));
    }
?>


<div class="wrap">

	<?php
        echo_test_page_title( get_admin_page_title() );

        echo_test_paragraph( "List all Facebook events since April in a table, please." );

        echo_test_section_title( "Table of Facebook Events" );

    ?>

    <div class="filterjs">
        <div class="sidebar_bar">
            <div class="sidebar_left_find">
                <div class="sidebar_list">
                    <h4>Search with filter.js</h4>
                    <input type="text" id="search_box" class="searchbox" placeholder="Type here...."/>
                </div>
            </div>
            <div class="sidebar_left_find">
                <div class="sidebar_list">
                    <h4>Filter by Permissions</h4>
                    <ul id="removed">
                        <li>
                            <input id="active" value="active" type="checkbox">
                            <span >Display</span>
                        </li>
                        <li>
                            <input id="inactive" value="inactive" type="checkbox">
                            <span>Removed</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="featured_events_find">
            <div class="featured_list_find" id="event_list"></div>
        </div>
        <div class="clearfix"></div>
    </div>

    <?php

        echo_test_button("Remove Event", "remove_event_button", 75);
        echo_test_button("Display Event", "display_event_button", 80);

    ?>

</div>
