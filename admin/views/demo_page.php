<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 13/06/14
 * Time: 10:15 PM
 */

// Extend the class
class Demo_Page extends AdminPageFramework {

    //public function start_Demo_Page() {
    public function start_Demo_Page() {

        $this->setFooterInfoLeft( '<br />Smelter Text on the left hand side.', false );
        $this->setFooterInfoRight( '<br />Smaller Text on the RIGHT hand side.', false );
    }

    // Define the setUp() method to set how many pages, page titles and icons etc.
    public function setUp() {

        // Creates the root menu
        $this->setRootMenuPage( 'Settings' );    // specifies to which parent menu to add.

        // Adds the sub menus and the pages
        $this->addSubMenuItems(
            array(
                'title'    =>    '1. My First Setting Page',    // page and menu title
                'page_slug'    =>    'my_first_settings_page'     // page slug
            )
        );

       // $this->setFooterInfoLeft( '<br />Yankee Text on the left hand side.', false );


    }

    // Notice that the name of the method is 'do_' + the page slug.
    // So the slug should not contain characters which cannot be used in function names such as dots and hyphens.
    public function do_my_first_settings_page() {

        ?>
        <h3>Action Hook</h3>
        <p>This is inserted by the 'do_' + page slug method.</p>
    <?php

//        $this->setFooterInfoLeft( '<br />Zero Text on the left hand side.', false );


    }

}