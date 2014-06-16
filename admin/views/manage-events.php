<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 13/06/14
 * Time: 10:15 PM
 */

// Extend the class
class Manage_Events extends AdminPageFramework {

    // Define the setUp() method to set how many pages, page titles and icons etc.
    public function setUp() {

        // Creates the root menu
        $this->setRootMenuPage( 'Events' );    // specifies to which parent menu to add.

        // Adds the sub menus and the pages
        $this->addSubMenuItems(
            array(
                'title'    =>    'Manage Events',    // page and menu title
                'page_slug'    =>    'manage_events_page'     // page slug
            )
        );

        $this->addSettingSections(
            'manage_events_page',
            array(
                'section_id' => 'modify_section',
                'title' => 'Modify Events',
                'description' => 'Change events or return them to their defaults.',
            )
        );

        $this->addSettingFields(
            'modify_section',
            array(	// Read-only
                'field_id'	=>	'test_event_name',
                'title'	=>	'Event Name',
                'type'	=>	'text',
                'default'	=>	'Placeholder text',
                'attributes'	=>	array(
                    'readonly'	=>	'ReadOnly',
                    // 'disabled'	=>	'Disabled',		// disabled can be specified like so
                ),
                'value'	=>	'Placeholder text',
                'description'	=>	'Original Name'
            ),
            array(	// Multiple text fields
                'field_id'	=>	'modify_host',
                'title'	=>	'Host Name',
                'help'	=>	'Modify the host name.',
                'type'	=>	'text',
                'default'	=>	'Placeholder text',
                'label'	=>	'Original Host:',
                'attributes'	=>	array(
                    'value'	=>	'modify old host',
                    'readonly'	=>	'ReadOnly',
                ),
                'delimiter'	=>	'<br />',
                array(
                    'default'	=>	'Placeholder new host',
                    'label'	=>	'New Host: ',
                    'attributes'	=>	array(
                        'field_id' => 'modify_host',
                        'readonly'	=>	false,
                    )
                ),
                //'description'	=>	'Modify the host.'
            ),
            array( // Submit button
                'field_id' => 'submit_button_b',
                'type' => 'submit',
                'show_title_column' => false,
            )
        );

    }

    /*public function start_Manage_Events() {

        $this->setFooterInfoLeft( '<br />Smelter Text on the left hand side.', false );
    }*/

    public function content_foot_manage_events_page( $sContent ) {

        echo $sContent;  //this is the title of the page set in the ::addSubMenuItems method above.
        ?>

        <h3 class="title">List of Facebook Events</h3>

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
        <div class="clearfix cf"></div>
        </div>

        <?php

        $this->echo_button("Remove Event", "remove_event_button", 25);
        $this->echo_button("Display Event", "display_event_button", 30);
        $this->echo_button("Modify Event", "modify_event_button", 35);

        echo "<hr>";


    }

    // Notice that the name of the method is 'do_' + the page slug.
    // So the slug should not contain characters which cannot be used in function names such as dots and hyphens.
    public function do_manage_events_page() {

        /* $this->setFooterInfoLeft( '<br />Orange Text on the left hand side.', false );*/

        ?>

        <h3 class="title">This is an H3 tag</h3>
        <p>And this is what are you doing with your life</p>


    <?php

    }

    private function echo_button( $button_text, $id, $tab_index ) {

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


}