(function ( $ ) {
	"use strict";

    /*
     http://stackoverflow.com/questions/280634/endswith-in-javascript
     */
    if (typeof String.prototype.endsWith !== 'function') {
        String.prototype.endsWith = function(suffix) {
            return this.indexOf(suffix, this.length - suffix.length) !== -1;
        };
    }

    $(document).ready(function($) {

        change_buttons();
        ajax_loading(true);


        $( ".dismiss_notice" ).bind( "click", function( event ) {
            event.preventDefault();
            $(this).parent().removeClass("updated error").empty();
        });

		// Place your administration-specific JavaScript here
        var $event_container = $('#event_list');
        var $event_rows = $('[id^="fjs_"]');

        $event_container.on("change", function() {

            ajax_loading(false);

            $(this).find('[id^="fjs_"]')
                .on("click", function() {
                click_row($(this));
            })
                .on("dblclick", modify_event_setup
            );

            $('#remove_event_button, #display_event_button')
                .on("click", ajax_return_to_or_remove_from_calendar);

            $('#modify_event_button').on("click", modify_event_setup);

            $(this).unbind( "change" );

            $(this).on("mouseenter", function() {
                $(this).focus();
            });
        })

    });

    function click_row($row) {

        var $event_rows = $('[id^="fjs_"]');

        var already_selected = ( $row.hasClass( "selected" ) ) ? true : false;

        $event_rows.removeClass( "selected" );

        if( ! already_selected ) {
            $row.addClass( "selected" );
        }

        change_buttons();
    }

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

    function disable_buttons() {
        var $event_buttons = $('[id$="_event_button"]');

        $event_buttons.prop("disabled", true);

        return $event_buttons;
    }

    function ajax_loading(loading) {

        var $loading_gif = $(".filterjs__loading");

        if(loading)
            $loading_gif.removeClass("hidden");
        else
            $loading_gif.addClass("hidden");
    }

    function ajax_return_to_or_remove_from_calendar() {

        //in_progress(true);

        var $selected_row = $("#event_list").find(".selected");
        var name = $selected_row.find(".name").text();
        var $button = $('input.button:enabled').first(); //hacky, but it works.
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
            .fail(function(data) {
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
            "ticket_uri_fb",
        ]

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

                $('.modify_' + key).val( values[key] );
                $('.modify_' + key).trigger( 'update' );  //can hook into this if necessary
            }
        }

    }

}(jQuery));