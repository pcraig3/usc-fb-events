/**
 * public-fitler.js contains the AjaxEventsPublic module which expects init-filter.js' AjaxEvents to be loaded beforehand.
 * It defines the HTML structure for the event template (as a plain string, which isn't really ideal but it's easy),
 * as well as provides the 'ajax_events_gotten' method that initialises everything once the events have been returned.
 *
 * Warning: this file isn't really meant to be used in it's current form.
 * If there was need of a searchable event list this provides a good base, but in its current form it's woefully unpolished.
 */

jQuery(function ($) {
    /* You can safely use $ in this code block to reference jQuery */

    var AjaxEventsPublic = (function (AjaxEvents) {

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
        var ajax_events_gotten = function( events ) {

        $('.filterjs__loading').addClass('hidden');

        fJS = filterInit( events );

        $('#event_list').trigger( "change" );
    };

        /**
         * function pretty much a straightforward copy of the samples given in the filter.js github page
         * 'view' defines the HTML event template, and 'settings' defines the filtering options, which for the
         * public events list is a search bar.
         *
         * @see: https://github.com/jiren/filter.js/tree/master
         *
         * @since     1.0.0
         *
         * @param events
         * @returns {*} a searchable list of events
         */
    var filterInit = function( events ) {

        var view = function( event ){

            //at this point we have ONE EVENT.  This sets up the loop.
            var html_string = "";

            var img_url = ( event.pic_big ) ? event.pic_big : "";
            var ticket_uri = ( event.ticket_uri ) ? event.ticket_uri : "";

            /* Figure out if the event has passed or not. */
            var past_or_upcoming_event = (AjaxEvents.is_upcoming_event( event )) ? "upcoming" : "past";

            html_string += '<div class="events__box clearfix ' + past_or_upcoming_event + '">';
            html_string +=  '<div class="flag">';
            html_string +=      '<div class="flag__image">';

            if(img_url) {
                html_string +=      '<img src="' + img_url + '">';
            }

            html_string +=      '</div><!--end of .flag__image-->';

            html_string +=      '<div class="flag__body">';
            html_string +=          '<h3 class="alpha" title="'
                                    + event.host + ": " + event.name + '">'
                                    + event.name + '</h3>';

            var date = new Date( parseInt(event.start_time) * 1000);

            html_string +=          '<p class="lede" data-start_time="' + event.start_time + '">' + date.toLocaleDateString() + ' | '
                                    + event.host + '</p>';

            html_string +=      '</div><!--end of .flag__body-->';

            html_string += '</div><!--end of .flag-->';

            if( ticket_uri ) {

                html_string += '<a href="' + ticket_uri + '" target="_blank">';
                html_string +=      '<div class="event__button" style="background:palevioletred;color:white;">Get Tickets</div>';
                html_string += '</a>';
            }

            html_string +=      '<a href="' + event.url + '" target="_blank">';
            html_string +=          '<span class="events__box__count">' + event.id + '</span>';
            html_string +=      '</a>';

            html_string += '</div><!--end of events__box-->';

            return html_string;
        }

        var settings = {
            /*filter_criteria: {
                removed: ['#removed :checkbox', 'removed']
            },*/
            search: {input: '#search_box' },
            //and_filter_on: true,
            id_field: 'id' //Default is id. This is only for usecase
        };

        return FilterJS(events, "#event_list", view, settings);
    }

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

        console.log(options);

        AjaxEventsPublic.ajax_get_events( options, AjaxEventsPublic.ajax_events_gotten );
    });

});

