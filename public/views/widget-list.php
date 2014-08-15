<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 29/07/14
 * Time: 11:34 AM
 */

ob_start();

$default_timezone = date_default_timezone_get();
date_default_timezone_set("America/Toronto");

?>

    <div id="events">
        <h1><?php echo $title; ?></h1>
        <a class="viewEvent" href="/events">View Events Calendar</a>

        <div class="filterjs">

            <div class="filterjs__list__wrapper">
                <div class="filterjs__loading filterjs__loading--ajax">
                    <img class="filterjs__loading__img" title="meow meow"
                         src="<?php echo plugins_url( '/' . $this->plugin_slug . '/assets/horse.gif') ?>" alt="Loading" height="91" width="160">
                    <!--p class="filterjs__loading__status">
                        * Loading *
                    </p-->
                    <!--a class="filterjs__loading__nojs" href="events-from-facebook-no-js/">Loading too slowly? Click here!</a-->
                </div>

                <div class="filterjs__list" id="event_list" data-nonce="<?php echo wp_create_nonce("event_list_nonce"); ?>"></div>
            </div>
            <div class="clearfix cf"></div>
        </div>

    </div>

<?php

date_default_timezone_set($default_timezone);

return ob_get_clean();

