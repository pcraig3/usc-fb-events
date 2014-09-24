/**
 * Sort of an odd partition of JS code, but there you go.
 * whereas admin-filter.js deals with getting/returning/displaying/filtering/counting events,
 * admin.js is concerned modifying the events in the database and setting up the events with the metadata to
 * make interface with the database
 */
(function ( $ ) {
    "use strict";

    /**
     * function extends the String class with an endsWith method.
     *
     * @see: http://stackoverflow.com/questions/280634/endswith-in-javascript
     */
    if (typeof String.prototype.endsWith !== 'function') {
        String.prototype.endsWith = function(suffix) {
            return this.indexOf(suffix, this.length - suffix.length) !== -1;
        };
    }

    /**
     * $.ready function sets up the backend on pageload and than attaches an event handler to the event list
     * so that when it changes, some stuff happens
     *
     * 1. Disable buttons
     * 2. Display loading horse
     * 3. Bind a click event to .dismiss_notice elements to hide generated WordPress notifications
     * 4. Add change event handler to event_list
     *      Event listener does the following:
     *          1. Remove Ajax loading horse when events are returned
     *          2. Add a click and a dblclick event to rows
     *          3. Add the ajax_return_to_or_remove_from_calendar function to appropriate buttons
     *          4. Add modify_event_setup method to 'modify event' button
     *          5. Add focus to event_list if it's moused-over.
     *
     * @since     1.1.0
     */
    $(document).ready(function($) {

        change_buttons();
        ajax_loading(true);

        $( ".dismiss_notice" ).bind( "click", function( event ) {
            event.preventDefault();
            $(this).parent().removeClass("updated error").empty();
        });

        // Place your administration-specific JavaScript here
        var $event_container = $('#event_list');
        //var $event_rows = $('[id^="fjs_"]');

        $event_container.on("change", function() {

            ajax_loading(false);

            $(this).find('[id^="fjs_"]')
                .on("click", function() {
                    click_row($(this));
                })
                .on("dblclick", modify_event_setup
                );
                //basically, anything that is constant shouldn't be assigned more event listeners.

                $('#remove_event_button, #display_event_button').unbind('click')
                    .on("click", ajax_return_to_or_remove_from_calendar);

                $('#modify_event_button').unbind('click').on('click', modify_event_setup);

                //$(this).unbind( "change" );

                $(this).unbind('mouseenter').on('mouseenter', function() {
                    $(this).focus();
                });
        })

    });

    /**
     * function controls what happens when an event row is clicked.
     * Basically, removes or applies the appropriate class and updates the buttons.
     *
     * @param $row  the event row which was clicked
     *
     * @since     1.0.0
     */
    function click_row($row) {

        var $event_rows = $('[id^="fjs_"]');

        var already_selected = ( $row.hasClass( "selected" ) ) ? true : false;

        $event_rows.removeClass( "selected" );

        if( ! already_selected ) {
            $row.addClass( "selected" );
        }

        change_buttons();
    }

    /**
     * function which decides which buttons should be click-able
     * If no row is currently selected, all buttons are disabled.
     * If a row denoting a removed element is selected, enable the 'return to calendar' button
     * Else, if a row has not been removed, enable both the 'remove' and 'modify' buttons
     *
     * @since     1.0.0
     */
    function change_buttons() {

        var $selected_row = $("#event_list").find(".selected");
        var $event_buttons = disable_buttons();

        if( $selected_row.length ) {

            //if the item has a class "removed": enable button return_event
            if($selected_row.hasClass('removed'))
            { $event_buttons.filter('#display_event_button').prop("disabled", false); }
            //	else: enable button remove_event
            else {
                $event_buttons.not('#display_event_button').prop("disabled", false);
            }
        }
    }

    /**
     * function disables all buttons and then returns them
     *
     * @returns {*|HTMLElement}     the event removal/modification buttons
     *
     * @since     1.0.0
     */
    function disable_buttons() {
        var $event_buttons = $('[id$="_event_button"]');

        $event_buttons.prop("disabled", true);

        return $event_buttons;
    }

    /**
     * function which either hides or reveals the ajax_loading horse depending on the passed-in parameter.
     *
     * @param loading   boolean true if we are loading something and want the horse visible. false to hide him.
     *
     * @since     1.0.0
     */
    function ajax_loading(loading) {

        var $loading_gif = $(".filterjs__loading");

        if(loading)
            $loading_gif.removeClass("hidden");
        else
            $loading_gif.addClass("hidden");
    }

    /**
     * function which which either removes an event from the calendar or returns an event to it.
     *
     * If the response indicates that the event has been removed/returned successfully, an appropriate
     * WordPress notification will be displayed above the event list.
     *
     * If the response indicates a failure, some sort of error message will be displayed as an alert.
     *
     * @since     1.0.0
     */
    function ajax_return_to_or_remove_from_calendar() {

        //in_progress(true);
        var $selected_row = $("#event_list").find(".selected");
        var name = $selected_row.find(".name").text();
        var $button = $(this); //find button even though it's not as clear as with an anonymous function
        var button_id = $button.attr("id");

        ajax_loading(true);
        disable_buttons();
        //console.log(button_id + ": " + $selected_row.find(".name").text());

        var $jqxhr = $.ajax({
            type: "POST",
            url: "admin-ajax.php",
            data: {
                action: 	"return_to_or_remove_from_calendar",
                eid:  		$selected_row.data("eid"),
                name: 		name,
                nonce: 		$button.data("nonce"),
                button_id: 	button_id
            },
            dataType: "json"
        });

        $jqxhr
            .done(function( data ) {

                //console.log(data);

                if(!data['success']) {
                    alert("Error performing " + button_id + " operation, bro.  Look for the problem in 'plugins/usc-fb-events/admin/assets/js/admin.js'");
                    return;
                }

                var update_msg = "<strong>" + name + "</strong> ";

                //"hide_me".lastIndexOf("hide", 0) <<this is 0
                //this code looks for "remove" as a prefix
                if ( button_id.lastIndexOf("remove", 0) === 0 ) {
                    //in in here, event has been removed.
                    $selected_row.addClass("removed");
                    $selected_row.find(".removed").text("removed");
                    update_msg += "has been removed from ";
                }
                else {
                    $selected_row.removeClass("removed");
                    $selected_row.find(".removed").text("display");
                    update_msg += "will once again be displayed on ";
                }

                update_msg += "the calendar.";
                $("#filterjs__notice").addClass("updated").empty().prepend("<p>" + update_msg + "</p><a class='dismiss_notice' style='cursor:pointer;'>Dismiss</a>");

                $selected_row.data("removed", $selected_row.find(".removed").text());

                //update_prohibited_event_number($wrap.find('#all_events option.removed').size());
            })
            .fail(function(){ //data) {
                alert( "Somehow '" + button_id + "' the event went all wrong.  Try reloading?" );
                //console.log(data);
                $("#filterjs__notice").addClass("updated").prepend("<p>Yikes! Maybe you should try reloading the page?</p>");

            })
            .always(function () {

                ajax_loading(false);
                $selected_row.removeClass("selected");
                change_buttons();

                $( ".dismiss_notice" ).bind( "click", function( event ) {
                    event.preventDefault();
                    $(this).parent().removeClass("updated error").empty();
                });
            });
    }

    /**
     * function which sets up the fields under the event list if the user has opted to modify an event.
     *
     * If the 'modify event' button is pressed, we need to fill the fields underneath the event list with both
     * the original values and the updated values (if any) so that original values can be modified, overwritten,
     * or restored.
     *
     * function uses the tons of meta-data on each event row to fill up the fields under the event.
     *
     * @since     1.0.0
     */
    function modify_event_setup() {

        var $selected_row = $("#event_list").find(".selected");

        //~ROW NAMES
        var values = {};
        values.eid = $selected_row.data("eid");
        values.name = $selected_row.data("name");
        values.host = $selected_row.data("host");
        values.host_fb = $selected_row.data("host_fb");
        values.start_time = $selected_row.data("start_time");
        values.start_time_fb = $selected_row.data("start_time_fb");
        values.location = $selected_row.data("location");
        values.location_fb = $selected_row.data("location_fb");
        values.ticket_uri = $selected_row.data("ticket_uri");
        values.ticket_uri_fb = $selected_row.data("ticket_uri_fb");
        values.url = $selected_row.data("url");
        values.url_fb = $selected_row.data("url_fb");
        values.modified = $selected_row.data("modified");
        values.removed = $selected_row.data("removed");

        $selected_row.removeClass("selected");

        var preserve_empty_values = [
            "ticket_uri_fb"
        ];

        //console.log(values);

        for (var key in values) {

            if (values.hasOwnProperty(key)) {

                //if the key ends with _fb and it is undefined and it is NOT in the preserve_empty_values array.
                if(key.endsWith("_fb") && values[key] === undefined && ($.inArray( key, preserve_empty_values ) < 0 ) ) {

                    //this is for situations where there is no host_fb
                    var key_without_fb = key.substring(0, key.length - "_fb".length);
                    values[key] = values[key_without_fb];
                    values[key_without_fb] = undefined;

                    $('.modify_' + key_without_fb).val( values[key_without_fb] );
                }

                //console.log(key);

                var $modify_key = $('.modify_' + key);
                $modify_key.val( values[key] );
                $modify_key.trigger( 'update' );  //can hook into this if necessary
            }
        }
        $( 'html, body' ).animate( { scrollTop : $( '#section-modify__0' ).offset().top - 60 }, 800 );
    }

}(jQuery));