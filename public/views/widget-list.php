<?php
/**
 * Created by PhpStorm.
 * User: Paul
 * Date: 29/07/14
 * Time: 11:34 AM
 */

ob_start();

?>

    <div class="filterjs">

        <div class="filterjs__list__wrapper">
            <div class="filterjs__loading filterjs__loading--ajax">
                <img class="filterjs__loading__img" title="meow meow"
                     src="<?php echo plugins_url( '/' . $this->plugin_slug . '/assets/cat.gif') ?>" alt="Loading" height="80" width="100">
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

