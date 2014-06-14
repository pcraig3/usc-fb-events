<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 13/06/14
 * Time: 10:15 PM
 */

// Extend the class
class Manage_Events extends AdminPageFramework {

    public function start_Manage_Events() {

        $this->setFooterInfoLeft( '<br />Smelter Text on the left hand side.', false );
    }

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

    }

    // Notice that the name of the method is 'do_' + the page slug.
    // So the slug should not contain characters which cannot be used in function names such as dots and hyphens.
    public function do_manage_events_page() {

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
    <?php

    }

}