<?php

/**
 * Check if request is a form submission from this page
 *  if submitted from form, basename http referer = "options-general.php?page=test-events"
 *  if not, there is no HTTP_REFERER set
 *
 * @param string $script script path
 */
if (strtoupper($_SERVER['REQUEST_METHOD']) === 'POST' &&
        false !== ( strpos( basename($_SERVER['HTTP_REFERER']), $_SERVER['QUERY_STRING'] ) ) ) {

    $values = array();

    if ( !wp_verify_nonce( $_POST['_wpnonce'], "test_form_nonce")) {
        exit("No naughty business please");
    }

    $values['eid'] =        ( isset($_POST['modify_eid']) ) ? $_POST['modify_eid'] : null;
    $values['name'] =       ( isset($_POST['modify_name']) ) ? $_POST['modify_name'] : null;
    $values['removed'] =    ( isset($_POST['modify_removed']) &&
                                $_POST['modify_removed'] === "display" ) ? 0 : 1;
    $values['host_old'] =   ( isset($_POST['modify_host_old']) ) ? $_POST['modify_host_old'] : null;
    $values['host'] =       ( isset($_POST['modify_host']) ) ? $_POST['modify_host'] : null; //add this if not there.

    /*
    echo "<pre>";
    var_dump($values);
    echo "</pre>";
    */

    if( isset($values['eid']) ) {

        DB_API::insert_on_duplicate_key_update(
            $values['eid'],
            array(
                'removed' =>    $values['removed'],
                'name' =>       ( isset($values['name']) ) ? $values['name'] : NULL,
                'host' =>       ( isset($values['host']) ) ? $values['host'] : NULL,
        ));

   }

}

?>

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

    function echo_test_form( $host ) {

        $first_section = "Event Name";
        $second_section = "Event Host";
        $host_modified = null;
        $name  = "";

        $html_string = '<form action="" method="post" id="test_form">';
        $html_string .= '<input id="_wpnonce" name="_wpnonce" value="' . wp_create_nonce("test_form_nonce") . '" type="hidden">';
        $html_string .= '<input id="modify_eid" name="modify_eid" value="" type="hidden">';
        $html_string .= '<input id="modify_removed" name="modify_removed" value="" type="hidden">';
        $html_string .= '<table class="form-table">';
        $html_string .=     '<tbody>';
        $html_string .=         '<tr>';
        $html_string .=             '<th scope="row">'. $first_section .'</th>';
        $html_string .=                 '<td>';
        $html_string .=                     '<fieldset>';
        $html_string .=                         '<legend class="screen-reader-text">';
        $html_string .=                             '<span>';
        $html_string .=                                 $first_section;
        $html_string .=                             '</span>';
        $html_string .=                         '</legend>';
        $html_string .=                             '<input id="modify_name" class="regular-text" type="text" value="' . $name . '" name="modify_name" readonly="readonly">';
        $html_string .=                                 '<p class="description">';
        $html_string .=                                     'Event name';
        $html_string .=                                 '</p>';
        $html_string .=                     '</fieldset>';
        $html_string .=                 '</td>';
        $html_string .=         '</tr>';
        $html_string .=         '<tr>';
        $html_string .=             '<th scope="row">'. $second_section .'</th>';
        $html_string .=                 '<td>';
        $html_string .=                     '<fieldset>';
        $html_string .=                         '<legend class="screen-reader-text">';
        $html_string .=                             '<span>';
        $html_string .=                                 $second_section;
        $html_string .=                             '</span>';
        $html_string .=                         '</legend>';
        $html_string .=                             '<input id="modify_host_old" class="regular-text" type="text" value="' . $host . '" name="modify_host_old" readonly="readonly">';
        $html_string .=                                 '<p class="description">';
        $html_string .=                                     'Original host name';
        $html_string .=                                 '</p>';
        $html_string .=                             '<br>';

        if($host_modified)
            $html_string .=                             '<input id="modify_host" class="regular-text" type="text" value="' . $host_modified . '" name="modify_host">';
        else
            $html_string .=                             '<input id="modify_host" class="regular-text" type="text" value="" placeholder="enter a new value" name="modify_host">';

        $html_string .=                                 '<p class="description">';
        $html_string .=                                     'New host name';
        $html_string .=                                 '</p>';
        $html_string .=                             '<br>';
        $html_string .=                     '</fieldset>';
        $html_string .=                 '</td>';
        $html_string .=         '</tr>';
        $html_string .=     '</tbody>';
        $html_string .= '</table>';

        echo $html_string;
        submit_button( "Modify Event", "primary", "modify_event_submit" );
        echo '<form/>';

    }

?>


<div class="wrap">

	<?php
        echo_test_page_title( get_admin_page_title() );

        echo_test_paragraph( "List all Facebook events since April in a table, please." );

        echo_test_section_title( "Table of Facebook Events" );

    ?>

    <div id='filterjs__notice'></div>

    <div class="filterjs">
        <div class="filterjs__filter">
            <div class="filterjs__filter__search__wrapper">
                    <h4>Search with filter.js</h4>
                    <input type="text" id="search_box" class="searchbox" placeholder="Type here...."/>
            </div>
            <div class="filterjs__filter__checkbox__wrapper">
                    <h4>Filter by Permissions</h4>
                    <ul id="removed">
                        <li>
                            <input id="display" value="display" type="checkbox">
                            <span >Display</span>
                        </li>
                        <li>
                            <input id="removed" value="removed" type="checkbox">
                            <span>Removed</span>
                        </li>
                    </ul>
            </div>
        </div>
        <div class="filterjs__list__wrapper">
            <div class="filterjs__loading">
                <img class="filterjs__loading__img"
                     src="/wp-content/plugins/test-events/assets/cat.gif" alt="Loading" height="80" width="100">
            </div>
            <div class="filterjs__list__crop">
                <div class="filterjs__list" id="event_list" data-nonce="<?php echo wp_create_nonce("event_list_nonce"); ?>"></div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>

    <?php

        echo_test_button("Remove Event", "remove_event_button", 75);
        echo_test_button("Display Event", "display_event_button", 80);
        echo_test_button("Modify Event", "modify_event_button", 85);

        echo_test_section_title( "Modify Event: " );

        echo_test_form("");

    ?>

</div>
