/**
 * Created by Paul on 29/07/14.
 */

jQuery(function ($) {
    /* You can safely use $ in this code block to reference jQuery */

    AjaxEvents.ajax_events_gotten = function( events, limit ) {

        $('.filterjs__loading').addClass('hidden');

       fJS = filterInit( events );

        $('#event_list').trigger( "change" );
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

            if(ticket_uri)
                html_string +=  '<a target="_blank" href="' + ticket_uri + '">Buy Tickets</a>';

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