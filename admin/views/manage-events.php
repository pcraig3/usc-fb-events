<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 13/06/14
 * Time: 10:15 PM
 */

// Extend the class
class Manage_Events extends AdminPageFramework {

    private $section_id = "modify";

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
                'section_id' => $this->section_id,
                'title' => 'Modify Events',
                'description' => 'Change events or return them to their defaults.',
            )
        );

        $this->addSettingFields(
            $this->section_id,
            /*array(
                'field_id'	=>	'eid',
                'type'	=>	'hidden',
                'attributes'	=>	array(
                    'class'     => $this->section_id . '_eid',
                ),
            ),
            array(
                'field_id'	=>	'removed',
                'type'	=>	'hidden',
                'attributes'	=>	array(
                    'class'     => $this->section_id . '_removed',
                ),
            ),*/
            array(	// Read-only
                'field_id'	=>	'name',
                'title'	=>	'Event Name',
                'type'	=>	'text',
                //'value'	=>	'old name',
                //'description'	=>	'Original Name'
                'attributes'	=>	array(
                    'class'     => $this->section_id . '_name',
                    'readonly'	=>	'ReadOnly',
                    // 'disabled'	=>	'Disabled',		// disabled can be specified like so
                ),

            ),
            array(	// Multiple text fields
                'field_id'	=>	'host',
                'title'	=>	'Host Name',
                'help'	=>	'Modify the host name.',
                'type'	=>	'text',
                //'default'	=>	'placeholder old host',
                'label'	=>	'Original Host:',
                'attributes'	=>	array(
                    'class'     => $this->section_id . '_host_old',
                    'value'	=>	'',
                    'readonly'	=>	'ReadOnly',
                ),
                'delimiter'	=>	'<br />',
                array(
                    'label'	=>	'New Host: ',
                    //'field_id' => 'host',
                    'attributes'	=>	array(
                        'class'     => $this->section_id . '_host',
                        'readonly'	=>	false,
                        'value'     =>  ''
                    )
                ),
                //'description'	=>	'Modify the host.'
            ),
            array(
                'field_id'	=>	'modify_event_submit',
                'type'	=>	'event_modify',
                'title'			=>	'Modify Events',
                'show_title_column' => false,
                'attributes'	=> array(
                    'class'	=>	'button button-primary modify_event_submit',
                ),
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

        //this is the end of the form defined in ::addSettingFields
        //submit_button( "Modify Event", "primary", "modify_event_submit" );

        ?>

        <h3 class="title">This is an H3 tag</h3>

    <?php

        echo $this->oDebug->getArray( get_option( 'Manage_Events' ) );

    }

    /**
     * The pre-defined validation callback method.
     *
     * @remark This is a pre-defined framework method.
     */
    public function validation_manage_events_page( $aInput, $aOldInput ) {	// validation_{page slug}

        //var_dump($aInput["modify"]["host_old"][1]);
        //echo "</pre>";

        //~ROW VALUES
        $values = array(
            'eid' =>            null,
            'name' =>           null,
            'host' =>           array(
                0   =>  null,
                1   =>  null,
            ),
           // 'start_time_old' => null,
           // 'start_time' =>     null,
        );


        foreach( $values as $key => $value ) {

            if( is_array($values[$key]) ) {

                //we only need the second element for this.
                $values[$key] = ( isset($aInput[$this->section_id][$key][1]) ) ? $aInput[$this->section_id][$key][1] : null;

            /*    foreach($value as $_key => $_value) {
                    $values[$key][$_key] = ( isset($aInput[$this->section_id][$value][$_key]) ) ? $aInput[$this->section_id][$value][$_key] : null;
                }
            */
            }
            else
                $values[$key] = ( isset($aInput[$this->section_id][$key]) ) ? $aInput[$this->section_id][$key] : null;
        }

        $values['removed'] =    ( isset($aInput[$this->section_id]['removed']) &&
            $aInput[$this->section_id]['removed'] === "display" ) ? 0 : 1;

        /*
        echo "<pre>";
        var_dump($aInput);
        var_dump($values);
        echo "</pre>";
        die();
        */

        if( isset($values['eid']) ) {

            DB_API::insert_on_duplicate_key_update(
                $values['eid'],
                array(
                    'removed' =>    $values['removed'],
                    'name' =>       ( isset($values['name']) ) ? $values['name'] : NULL,
                    'host' =>       ( isset($values['host']) ) ? $values['host'] : NULL,
                  //  'start_time' =>       ( isset($values['start_time']) ) ? $values['start_time'] : NULL,
                ));

        }

        return $aInput;
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