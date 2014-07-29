/**
 * Created by Paul on 29/07/14.
 */

jQuery(function ($) {
    /* You can safely use $ in this code block to reference jQuery */

    var fJS;

    $(document).ready(function($) {

        ajax_get_events( options );
    });

    /**
     * Function sets up our list in the backend.
     * Gets the eids of removed events and merges them with the data from Facebook.
     *
     * @param events    a JSON list of Facebook Events
     *
     * @since   0.9.5
     */
    function ajax_get_events( options ) {

        var api_url = options.api_url || "testwestern.com/api/events/events/2014-04-01"; //@TODO: this date is sort of arbitrary
        var limit = options.limit || 0;

        // Assign handlers immediately after making the request,
        // and remember the jqxhr object for this request
        var jqxhr = $.post(
            options.ajax_url,
            {
                action:         "get_events",
                api_url:        api_url,
                attr_id:        "event_list",
                nonce:          $("#event_list").data("nonce"),
                remove_events:  1
                //we don't need this column because it defaults to false.
                //whitelist: 0
            },

            function( data ) {

                //console.log(data);

                if(! data['success']) {
                    alert("Ack! Problems getting your removed events back from the database.")
                }

                var events = data['response']['events'];

                $.each(events, function(i, event){
                    event.id = i+1;

                    if( parseInt(event.removed) === 1 ) {
                        event.removed = "removed";
                    }
                    else
                    if ( parseInt(event.modified) === 1 ){
                        event.removed = "modified";
                    }
                    else
                        event.removed = "display";
                });

                ajax_events_gotten( events, limit );

            }, "json");
        /*.fail(function() {
         alert( "error" );
         })
         .always(function() {
         alert( "finished" );
         });*/
    }


   function ajax_events_gotten( events, limit ) {

        $('.filterjs__loading').addClass('hidden');

        var usc_events = remove_non_usc_events( events, limit );

        fJS = filterInit( usc_events );

        $('#event_list').trigger( "change" );
    }

    function remove_non_usc_events( events, limit ) {

        /*
         name: "University Students' Council of Western"
         page_id: "153227864727516"

         name: "The Wave & Spoke"
         page_id: "206752339379800"
         */
        var university_students_council_of_western = "153227864727516";
        var the_wave_and_spoke = "206752339379800";
        var usc_events = [];

        $.each( events, function( key, value ) {

            var event_creator = value.creator.toString();

            if(event_creator === university_students_council_of_western
                || event_creator === the_wave_and_spoke)
                usc_events.push(value);

        });

        if( limit < usc_events.length )
            usc_events = usc_events.slice(0, limit);

        return usc_events;
    }


    function filterInit( events ) {

        var view = function( event ){

            var days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
            var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];

            var html_string = "";

            //at this point we have ONE EVENT.  This sets up the loop.

            var img_url = ( event.pic_big ) ? event.pic_big : "http://placehold.it/400x200";
            var ticket_uri = ( event.ticket_uri ) ? event.ticket_uri : "";
            var location = ( event.location ) ? event.location : "";

            html_string += ' <div class="eventItem">';

            //if(img_url) {
            html_string += '<img class="hidden-xs" src="' + img_url + '" alt="" />';
            //}

            html_string += '<div class="eventTitle">';
            html_string += '<h2>' + event.name + '</h2>';

            var date = new Date( parseInt(event.start_time) * 1000);

            html_string +=  '<h3>' + days[ date.getDay() ] + ', ' + months[ date.getMonth() ] + date.getDate() + '</h3>';

            //Slice: positive #s are relative to the beginning, negative numbers are relative to the end.
            var localeTime = date.toLocaleTimeString();
            html_string +=  localeTime.slice(0, 4) + " " + localeTime.slice(-2);
            html_string +=  ' ';

            if(location) {
                html_string +=  '| @ ' + location;
            }

            html_string +=  '</div>';

            html_string +=  '<div class="eventLinks">';
            html_string +=  '<a target="_blank" href="#">Buy Tickets</a>';
            html_string +=  '<a target="_blank" href="' + event.url + '">View Event</a>';
            html_string +=  '</div>';

            html_string +=  '</div><!--end of eventItem-->';

            return html_string;

        }

        var settings = {
            /*filter_criteria: {
             removed: ['#removed :checkbox', 'removed']
             },*/
            //search: {input: '#search_box' },
            //and_filter_on: true,
            id_field: 'id' //Default is id. This is only for usecase
        };

        return FilterJS(events, "#event_list", view, settings);
    }

});