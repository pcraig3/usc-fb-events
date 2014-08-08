/**
 * Created by Paul on 29/07/14.
 */

jQuery(function ($) {
    /* You can safely use $ in this code block to reference jQuery */

    AjaxEvents.ajax_events_gotten = function( events, limit ) {

        $('.filterjs__loading').addClass('hidden');

       /* @TODO: This is bad infrastructure */
       var usc_events = AjaxEvents.remove_non_usc_events( events );

        /* @TODO: ideally, you cut down on the event array before processing it, but the API is making that harder. */
       usc_events = AjaxEvents.limit_events( usc_events, limit );

       fJS = filterInit( usc_events );

        $('#event_list').trigger( "change" );
    };

    AjaxEvents.remove_non_usc_events = function( events ) {

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

        return usc_events;
    };


    function filterInit( events ) {

        var view = function( event ){

            var days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
            var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];

            var html_string = "";

            //at this point we have ONE EVENT.  This sets up the loop.

            var img_url = ( event.pic_big ) ? event.pic_big : "http://placehold.it/400x200";
            var ticket_uri = ( event.ticket_uri ) ? event.ticket_uri : "";
            var location = ( event.location ) ? event.location : "";

            var past_or_upcoming_event = (AjaxEvents.is_upcoming_event( event )) ? "upcoming" : "past";

            html_string += ' <div class="eventItem ' + past_or_upcoming_event + '">';

            //if(img_url) {
            html_string += '<img class="hidden-xs" src="' + img_url + '" alt="" />';
            //}

            html_string += '<div class="eventTitle">';
            html_string += '<h2>' + event.name + '</h2>';

            var date = new Date( parseInt(event.start_time) * 1000);

            html_string +=  '<h3>' + days[ date.getDay() ] + ', ' + months[ date.getMonth() ]
                + " " + date.getDate() + '</h3>';

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

    $(document).ready(function($) {

        //$('#removed :checkbox').prop('checked', true);

        AjaxEvents.ajax_get_events( options );
    });

});