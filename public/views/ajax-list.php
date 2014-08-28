<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 17/06/14
 * Time: 7:22 PM
 */

    ob_start();

?>
    <h3 class="title">List of Facebook Events</h3>

    <div class="filterjs">
        <div class="filterjs__filter">
            <div class="filterjs__filter__search__wrapper">
                <h4>Search with filter.js</h4>
                <input type="text" id="search_box" class="searchbox" placeholder="Type here...."/>
            </div>
            <!--div class="filterjs__filter__checkbox__wrapper">
                <h4>Filter by Status</h4>
                <ul id="removed">
                    <li>
                        <input id="display" value="display" type="checkbox">
                        <span>Display</span>
                    </li>
                    <li>
                        <input id="modified" value="modified" type="checkbox">
                        <span>Modified</span>
                    </li>
                    <li>
                        <input id="removed" value="removed" type="checkbox">
                        <span>Removed</span>
                    </li>
                </ul>
            </div-->
        </div>
        <br>
        <div class="filterjs__list__wrapper">
            <div class="filterjs__loading filterjs__loading--ajax">
                <img class="filterjs__loading__img" title="go mustangs!"
                     src="<?php echo plugins_url( '/' . $this->plugin_slug . '/assets/horse.gif') ?>" alt="Loading" height="91" width="160">
                <p class="filterjs__loading__status">
                    * Loading *
                </p>
                <a class="filterjs__loading__nojs" href="events-from-facebook-no-js/">Loading too slowly? Click here!</a>
            </div>

            <!--div class="filterjs__list__crop"-->
            <div class="filterjs__list" id="event_list" data-nonce="<?php echo wp_create_nonce("event_list_nonce"); ?>"></div>
            <!--/div-->
        </div>
        <div class="clearfix cf"></div>
    </div>

<?php

    return ob_get_clean();

