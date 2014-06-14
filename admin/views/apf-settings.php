<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 09/06/14
 * Time: 7:02 PM
 */

// Extend the class
class APF_SettingsPage extends AdminPageFramework {

    public function __construct() {

        parent::__construct();

        $this->setFooterInfoLeft( '<br />Foxtrot Text on the left hand side.', false );
    }

        // Define the setUp() method to set how many pages, page titles and icons etc.
    public function setUp() {

        // Creates the root menu
        $this->setRootMenuPage( 'Settings' );    // specifies to which parent menu to add.

        // Adds the sub menus and the pages
        $this->addSubMenuItems(
            array(
                'title'    =>    'Test Events Settings',    // page and menu title
                'page_slug'    =>    "test_events",     // page slug
            )
        );

        $this->addSettingSections(
            $this->slug,
            array(
                'section_id' => 'my_first_section',
                'title' => 'My First Form Section',
                'description' => 'This section is for text fields.',
            ),
            array(
                'section_id' => 'my_second_section',
                'title' => 'My Second Form Section',
                'description' => 'This section is for selectors.',
            )
        );

        // Add form fields
        $this->addSettingFields(
            'my_first_section',
            array(
                'field_id' => 'my_text_field',
                'type' => 'text',
                'title' => 'Text',
                'description' => 'To create multiple fields of the same type, add arrays with numeric keys.',
                array(),    // sub-field
                array(),    // sub-field
            ),
            array(
                'field_id' => 'my_repeatable_text_field',
                'type' => 'text',
                'title' => 'Text',
                'repeatable' => true,
            )
        );
        $this->addSettingFields(
            'my_second_section',
            array(
                'field_id'    =>    'my_checkbox',
                'type'    =>    'checkbox',
                'title'    =>    __( 'Checkbox', 'admin-page-framework-demo' ),
                'label'    =>    __( 'Check me.', 'admin-page-framework-demo' ),
                'default'    =>    false,
            ),
            array(
                'field_id'    =>    'my_dropdown_list',
                'title'    =>    __( 'Drop-down List', 'admin-page-framework-demo' ),
                'type'    =>    'select',
                'default'    =>    1,    // the index key of the label array below which yields 'Two'.
                'label'    =>    array(
                    0    =>    'One',
                    1    =>    'Two',
                    2    =>    'Three',
                ),
            ),
            array(
                'field_id'    =>    'radio',
                'title'    =>    __( 'Radio Button', 'admin-page-framework-demo' ),
                'type'    =>    'radio',
                'label'    =>    array(
                    'a'    =>    'Apple',
                    'b'    =>    'Banana',
                    'c'    =>    'Cherry'
                ),
                'default'    =>    'c',    // yields Cherry; its key is specified.
                'after_label'    =>    '<br />',
            ),
            array( // Submit button
                'field_id' => 'submit_button_b',
                'type' => 'submit',
                'show_title_column' => false,
            )
        );

        //$this->setFooterInfoLeft( '<br />Yellow Text on the left hand side.', false );

    }

    /*public function do_after_test_events() {

        $this->setFooterInfoLeft( '<br />Red Text on the left hand side.', false );

    }

    public function content_bottom_test_events() {

        $this->setFooterInfoLeft( '<br />Amber Text on the left hand side.', false );

    }
    */

    // Notice that the name of the method is 'do_' + the page slug.
    // So the slug should not contain characters which cannot be used in function names such as dots and hyphens.
    public function do_test_events() {    // do_ + page slug

        // Show the saved option value.
        // The extended class name is used as the option key. This can be changed by passing a custom string to the constructor.
        echo '<h3>Saved Values</h3>';
        echo '<h3>Show as an Array</h4>';
        echo $this->oDebug->getArray( get_option( 'APF_SettingsPage' ) );
        echo '<h3>Retrieve individual field values</h4>';
        echo '<pre>APF_SettingsPage[my_first_section][my_text_field][0]: ' . AdminPageFramework::getOption( 'APF_SettingsPage', array( 'my_first_section', 'my_text_field', 0 ), 'default' ) . '</pre>';
        echo '<pre>APF_SettingsPage[my_second_section][my_dropdown_list]: ' . AdminPageFramework::getOption( 'APF_SettingsPage', array( 'my_second_section', 'my_dropdown_list' ), 'default' ) . '</pre>';

    }

}