/**
 * public-widget.js contains the AjaxEventsWidget module which expects init-filter.js' AjaxEvents to be loaded beforehand.
 * It defines the HTML structure for the event template (as a plain string, which isn't really ideal but it's easy),
 * as well as provides the 'ajax_events_gotten' method that initialises everything once the events have been returned.
 */

jQuery(function ($) {
    /* You can safely use $ in this code block to reference jQuery */

    var AjaxEventsWidget = (function (AjaxEvents) {

        /**
         * What to do once events have been received from Facebook
         *
         * 1. Hide loading horse
         * 2. Build event list using filter.js
         * 3. Trigger a change event on the list element
         *
         * @since     1.0.0
         * @param events
         */
        var ajax_events_gotten = function ( events ) {

            $('.filterjs__loading').addClass('hidden');

            fJS = filterInit( events );

            $('#event_list').trigger( "change" );

        };

        /**
         * function pretty much a straightforward copy of the samples given in the filter.js github page
         * 'view' defines the HTML event template, and 'settings' defines the filtering options, which for the
         * events widget is nothing.
         *
         * @see: https://github.com/jiren/filter.js/tree/master
         *
         * @since     1.0.0
         *
         * @param events
         * @returns {*} a list of events
         */
        var filterInit = function( events ) {

            var view = function( event ){

                var days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];

                var html_string = "";

                //at this point we have ONE EVENT.  This sets up the loop.

                var img_url = ( event.pic_big ) ? event.pic_big : "http://placehold.it/400x200";
                var ticket_uri = ( event.ticket_uri ) ? event.ticket_uri : "";
                var location = ( event.location ) ? event.location : "";

                var past_or_upcoming_event = (AjaxEvents.is_upcoming_event( event )) ? "upcoming" : "past";

                html_string += '<div class="eventItem ' + past_or_upcoming_event + '">';

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
                var firstColonPosition = localeTime.indexOf(":");
                html_string +=  localeTime.slice(0, firstColonPosition + 3) + " " + localeTime.slice(-2);
                html_string +=  ' ';

                if(location) {
                    html_string +=  '| @ ' + location;
                }

                html_string +=  '</div>';

                html_string +=  '<div class="eventLinks">';

                var ticket_class = (ticket_uri) ? 'half_size' : 'full_size';

                html_string +=  '<a class="' + ticket_class + '" target="_blank" href="' + event.url + '">View Event</a>';

                if(ticket_uri)
                    html_string +=  '<a class="' + ticket_class + '" target="_blank" href="' + ticket_uri + '">Buy Tickets</a>';


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

        /**
         * @since     1.0.0
         */
        return {
            ajax_events_gotten: ajax_events_gotten,
            ajax_get_events: AjaxEvents.ajax_get_events
        };

    })(AjaxEvents || {});

    /**
     * As soon as page is loaded, call the get_events function to request Facebook events.
     *
     * @since     1.0.0
     */
    $(document).ready(function($) {

        //$('#removed :checkbox').prop('checked', true);

        console.log( options );

        AjaxEventsWidget.ajax_get_events( options, AjaxEventsWidget.ajax_events_gotten );
    });

});