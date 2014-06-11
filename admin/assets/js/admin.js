(function ( $ ) {
	"use strict";



    $(document).ready(function($) {

        change_buttons();

		// Place your administration-specific JavaScript here
        var $event_container = $('#event_list');
        var $event_rows = $('[id^="fjs_"]');

        $event_container.on("change", function() {

            $(this).delegate( '[id^="fjs_"]', "click", function() {
                click_row($(this));
            });

            $('[id$="_event_button"]').on("click", ajax_return_to_or_remove_from_calendar);

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

            var button_id = "_event_button";

            //if the item has a class "removed": enable button return_event
            if($selected_row.hasClass('removed'))
            { button_id = 'display' + button_id; }
            //	else: enable button remove_event
            else
            { button_id = 'remove' + button_id; }

            $event_buttons.filter('#' + button_id).prop("disabled", false);

        }
    }

    function disable_buttons() {
        var $event_buttons = $('[id$="_event_button"]');

        $event_buttons.prop("disabled", true);

        return $event_buttons;
    }

    function ajax_return_to_or_remove_from_calendar() {

        //in_progress(true);

        var $selected_row = $("#event_list").find(".selected");
        var name = $selected_row.find(".name").text();
        var $button = $('input.button:enabled');
        var button_id = $button.attr("id");

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
                    alert("Error performing " + button_id + " operation, bro.  Look for the problem in 'plugins/test-events/admin/assets/js/admin.js'");
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
                $("#test_notice").addClass("error").empty().prepend("<p>" + update_msg + "</p><a id='dismiss_notice' style='cursor:pointer;'>Dismiss</a>");

                //update_prohibited_event_number($wrap.find('#all_events option.removed').size());
            })
            .fail(function(data) {
                alert( "Somehow '" + button_id + "' the event went all wrong.  Try reloading?" );
                //console.log(data);
                $("#test_notice").addClass("updated").prepend("<p>Yikes! Maybe you should try reloading the page?</p>");

            })
            .always(function () {

                $selected_row.removeClass("selected");
                change_buttons();


                $( "#dismiss_notice" ).bind( "click", function( event ) {
                    event.preventDefault();
                    $(this).parent().removeClass("updated error").empty();
                });
            });
    }

}(jQuery));